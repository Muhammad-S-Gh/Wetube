<?php

namespace App\Jobs\Video;

use App\Enums\UploadStateEnum;
use App\Http\Middleware\TrackQueueWaitTime;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Media;
use App\Models\Notification;
use App\Models\Video;
use Iamfarhad\Prometheus\Facades\Prometheus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FinalizeUploadJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;
    public int $tries = 1;
    public int $timeout = 120;
    public bool $deleteWhenMissingModels = true;
    protected ?string $thumbnailPublicId = null;
    public $dispatched_at;
    public string $pipeline = 'ffmpeg';



    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $videoId,
        protected string $videoPath,
        protected string $imagePath,
        protected string $token,
    ) {
        $this->dispatched_at = microtime(true);
    }
    public function middleware(): array
    {
        return [new TrackQueueWaitTime];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $prefix = "videos/{$this->videoId}/{$this->token}/";
        $hasEncodedVideoMedia = Media::query()
            ->where('mediable_type', Video::class)
            ->where('mediable_id', $this->videoId)
            ->where('resource_type', 'video')
            ->where('public_id', 'like', $prefix . '%')
            ->exists();

        if (!$hasEncodedVideoMedia) {

            $allMedia = Media::query()
                ->where('mediable_type', Video::class)
                ->where('mediable_id', $this->videoId)
                ->get();

            Log::error('Missing encoded media for finalize', [
                'video_id' => $this->videoId,
                'token' => $this->token,
                'prefix' => $prefix,
                'total_media_records' => $allMedia->count(),
                'media_types' => $allMedia->pluck('resource_type')->unique()->toArray(),
            ]);

            throw new \RuntimeException('Missing encoded media for current processing token, abort finalize');
        }

        try {
            if (Storage::disk('uploads_tmp')->exists($this->imagePath)) {
                try {
                    $path = Storage::disk('uploads_tmp')->path($this->imagePath);

                    $cloudinaryImage = (new UploadApi())->unsignedUpload(
                        $path,
                        config('filesystems.disks.cloudinary.images'),
                        ['resource_type' => 'image']
                    );

                    $this->thumbnailPublicId = $cloudinaryImage['public_id'] ?? null;

                    foreach (['public_id', 'secure_url', 'resource_type', 'format'] as $requiredKey) {
                        if (!isset($cloudinaryImage[$requiredKey]) || $cloudinaryImage[$requiredKey] === '') {
                            throw new \RuntimeException("Cloudinary image response missing key: {$requiredKey}");
                        }
                    }

                    Media::create([
                        'mediable_type' => Video::class,
                        'mediable_id'   => $this->videoId,
                        'disk'          => config('filesystems.disks.cloudinary.driver'),
                        'path'          => $cloudinaryImage['secure_url'],
                        'public_id'     => $cloudinaryImage['public_id'],
                        'resource_type' => $cloudinaryImage['resource_type'],
                        'format'        => $cloudinaryImage['format'],
                        'quality'       => ($cloudinaryImage['width'] ?? '') . 'x' . ($cloudinaryImage['height'] ?? ''),
                        'cloud_response' => json_encode($cloudinaryImage)
                    ]);
                } catch (Throwable $thumbnailException) {
                    Log::warning('Thumbnail finalize step failed and was ignored', [
                        'video_id' => $this->videoId,
                        'error' => $thumbnailException->getMessage(),
                    ]);

                    throw $thumbnailException;
                }
            }

            Video::where('id', $this->videoId)->update([
                'processed' => UploadStateEnum::COMPLETED,
                'processing_token' => null,
            ]);

            $video = Video::query()->find($this->videoId);
            $duration = now()->diffInSeconds($video->created_at);

            Prometheus::histogram('upload_duration_seconds')
                ->observe(
                    $duration,
                    [
                        'ffmpeg',
                    ]
                );

            Prometheus::counter('uploads_completed_total')->inc(['ffmpeg']);

            if ($video) {
                Notification::query()->create([
                    'user_id' => $video->user_id,
                    'notification' => 'site.notification_video_upload_completed',
                    'success' => true,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Finalize video failed', [
                'video_id' => $this->videoId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        } finally {
            Storage::disk('uploads_tmp')->delete($this->videoPath);
            Storage::disk('uploads_tmp')->delete($this->imagePath);
        }
    }


    protected function cleanupCloudinaryAssets(): void
    {
        $prefix = "videos/{$this->videoId}/{$this->token}";

        try {
            $admin = new AdminApi();

            foreach (['video', 'raw', 'subtitles'] as $resourceType) {
                $options = [
                    'resource_type' => $resourceType,
                    'type' => 'upload',
                ];

                do {
                    $result = $admin->deleteAssetsByPrefix($prefix, $options);
                    $nextCursor = $result['next_cursor'] ?? null;

                    if ($nextCursor) {
                        $options['next_cursor'] = $nextCursor;
                    } else {
                        unset($options['next_cursor']);
                    }
                } while ($nextCursor);
            }
        } catch (Throwable $cleanupException) {
            Log::warning('Cloudinary cleanup failed after finalize upload failure', [
                'video_id' => $this->videoId,
                'token' => $this->token,
                'error' => $cleanupException->getMessage(),
            ]);
        }

        if ($this->thumbnailPublicId) {
            try {
                (new UploadApi())->destroy($this->thumbnailPublicId, ['resource_type' => 'image']);
            } catch (Throwable $cleanupException) {
                Log::warning('Cloudinary thumbnail cleanup failed after finalize upload failure', [
                    'video_id' => $this->videoId,
                    'public_id' => $this->thumbnailPublicId,
                    'error' => $cleanupException->getMessage(),
                ]);
            }
        }
    }


    public function failed(?Throwable $exception): void
    {
        $this->cleanupCloudinaryAssets();

        $video = Video::query()->find($this->videoId);

        if ($video) {
            $video->update([
                'processed' => UploadStateEnum::FAILED->value,
                'processing_token' => null,
            ]);

            Notification::query()->create([
                'user_id' => $video->user_id,
                'notification' => 'site.notification_video_upload_failed',
                'success' => false,
            ]);
        }

        Prometheus::counter('uploads_failed_total')
            ->inc(['ffmpeg']);

        Log::error('FinalizeUploadJob exhausted retries', [
            'video_id' => $this->videoId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
