<?php

namespace App\Jobs\Video;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Enums\UploadStateEnum;
use App\Http\Middleware\TrackQueueWaitTime;
use App\Models\Media;
use App\Models\Notification;
use App\Models\Video;
use App\Services\CloudinaryMediaService;
use App\Services\ModerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Api\Upload\UploadApi;
use Iamfarhad\Prometheus\Facades\Prometheus;
use Intervention\Image\ImageManager;
use Throwable;

class OptimizedUploadJob implements ShouldQueue
{
    use Queueable;
    public int $tries = 1;
    public int $timeout = 300;
    public bool $deleteWhenMissingModels = true;
    public $dispatched_at;
    public string $pipeline = 'optimized';

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $videoId,
        protected string $videoPath,
        protected string $imagePath,
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
    public function handle(ModerationService $moderation, CloudinaryMediaService $cloudinary): void
    {
        $video = Video::find($this->videoId);

        if (!$video || $video->processed !== UploadStateEnum::PROCESSING) {
            Log::info('Skipping optimized moderation job — video is no longer processing', [
                'video_id' => $this->videoId,
            ]);

            return;
        }

        if (
            !Storage::disk('uploads_tmp')->exists($this->imagePath)
            || !Storage::disk('uploads_tmp')->exists($this->videoPath)
        ) {
            $this->failModeration($video, 'asset', 'file_not_found');

            return;
        }

        $imageResult = $moderation->moderateImage($this->imagePath);

        if (!($imageResult['pass'] ?? false)) {
            $this->failModeration($video, 'thumbnail', (string) ($imageResult['reason'] ?? 'unknown'));

            return;
        }

        $videoResult = $moderation->moderateVideo($this->videoPath);

        if (!($videoResult['pass'] ?? false)) {
            $this->failModeration($video, 'video', (string) ($videoResult['reason'] ?? 'unknown'));

            return;
        }

        $videoAbsolutePath = Storage::disk('uploads_tmp')->path($this->videoPath);
        $imageAbsolutePath = Storage::disk('uploads_tmp')->path($this->imagePath);

        $videoPublicId = null;
        $imagePublicId = null;
        $resizedImagePath = null;

        try {
            $rawVideo = $cloudinary->uploadVideoFromPath($videoAbsolutePath, $this->videoId);
            $videoPayload = $cloudinary->normalizeVideoPayload($rawVideo);
            $videoPublicId = $videoPayload['public_id'];

            $resizedImagePath = $this->resizeThumbnailForUpload($imageAbsolutePath);
            $rawImage = $cloudinary->uploadImageFromPath($resizedImagePath, $this->videoId);
            $imagePayload = $cloudinary->normalizeImagePayload($rawImage);
            $imagePublicId = $imagePayload['public_id'];

            $durationSeconds = (float) $videoPayload['duration'];

            $video->update([
                'hours' => (string) floor($durationSeconds / 3600),
                'minutes' => (string) floor(($durationSeconds / 60) % 60),
                'seconds' => (string) ($durationSeconds % 60),
                'longitudinal' => $videoPayload['height'] > $videoPayload['width'],
                'quality' => (string) $videoPayload['height'],
                'processed' => UploadStateEnum::COMPLETED,
                'processing_token' => null,
            ]);

            $duration = now()->diffInSeconds($video->created_at);
            Prometheus::histogram('upload_duration_seconds')
                ->observe(
                    $duration,
                    [
                        'optimized',
                    ]
                );

            Prometheus::counter('uploads_completed_total')
                ->inc(['optimized']);

            Media::create([
                'mediable_type' => Video::class,
                'mediable_id' => $video->id,
                'disk' => config('filesystems.disks.cloudinary.driver'),
                'path' => $videoPayload['secure_url'],
                'public_id' => $videoPayload['public_id'],
                'resource_type' => $videoPayload['resource_type'],
                'format' => $videoPayload['format'],
                'quality' => (string) $videoPayload['height'],
                'cloud_response' => json_encode($videoPayload['raw']),
            ]);

            Media::create([
                'mediable_type' => Video::class,
                'mediable_id' => $video->id,
                'disk' => config('filesystems.disks.cloudinary.driver'),
                'path' => $imagePayload['secure_url'],
                'public_id' => $imagePayload['public_id'],
                'resource_type' => $imagePayload['resource_type'],
                'format' => $imagePayload['format'],
                'quality' => $imagePayload['width'] . 'x' . $imagePayload['height'],
                'cloud_response' => json_encode($imagePayload['raw']),
            ]);

            Notification::create([
                'user_id' => $video->user_id,
                'notification' => 'site.notification_video_upload_completed',
                'success' => true,
            ]);
        } catch (Throwable $e) {
            $this->cleanupCloudinaryAssets($videoPublicId, $imagePublicId);

            if ($video->processed === UploadStateEnum::PROCESSING) {
                $video->update([
                    'processed' => UploadStateEnum::FAILED->value,
                    'processing_token' => null,
                ]);

                Notification::create([
                    'user_id' => $video->user_id,
                    'notification' => 'site.notification_video_upload_failed',
                    'success' => false,
                ]);

                Prometheus::counter('uploads_failed_total')->inc(['optimized']);

                Log::error('Optimized Cloudinary upload failed', [
                    'video_id' => $this->videoId,
                    'error' => $e->getMessage(),
                ]);
            }
        } finally {
            $this->deleteLocalFile($resizedImagePath);
            $this->deleteTempFiles();
        }
    }

    protected function deleteLocalFile(?string $path): void
    {
        if (!$path || !file_exists($path)) {
            return;
        }

        try {
            unlink($path);
        } catch (Throwable $e) {
            Log::warning('Failed to delete resized optimized thumbnail', [
                'video_id' => $this->videoId,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function failModeration(Video $video, string $assetType, string $reason): void
    {
        Log::warning('Optimized upload moderation failed', [
            'video_id' => $video->id,
            'asset_type' => $assetType,
            'reason' => $reason,
        ]);

        $video->update([
            'title' => 'BLOCKED',
            'processed' => UploadStateEnum::FAILED,
            'processing_token' => null,
        ]);

        Notification::create([
            'user_id' => $video->user_id,
            'notification' => $this->notificationKeyForReason($reason),
            'success' => false,
        ]);

        $this->deleteTempFiles();

        Prometheus::counter('uploads_failed_total')->inc(['optimized']);
    }


    protected function failUpload(Video $video, string $message): void
    {
        $video->update([
            'processed' => UploadStateEnum::FAILED,
            'processing_token' => null,
        ]);

        Notification::create([
            'user_id' => $video->user_id,
            'notification' => 'site.notification_video_upload_failed',
            'success' => false,
        ]);

        Log::error('optimized Cloudinary upload aborted', [
            'video_id' => $this->videoId,
            'message' => $message,
        ]);

        Prometheus::counter('uploads_failed_total')->inc(['optimized']);
    }

    protected function resizeThumbnailForUpload(string $imageAbsolutePath): string
    {
        $thumb = config('cloudinary.thumbnail');
        $tempPath = tempnam(sys_get_temp_dir(), 'wetube_thumb_');

        if ($tempPath === false) {
            throw new \RuntimeException('Unable to create temporary thumbnail file.');
        }

        $resizedPath = $tempPath . '.jpg';

        try {
            ImageManager::imagick()
                ->read($imageAbsolutePath)
                ->resize((int) $thumb['width'], (int) $thumb['height'])
                ->encode()
                ->save($resizedPath);
        } catch (Throwable $e) {
            $this->deleteLocalFile($tempPath);
            $this->deleteLocalFile($resizedPath);

            throw $e;
        }

        $this->deleteLocalFile($tempPath);

        return $resizedPath;
    }


    protected function cleanupCloudinaryAssets(?string $videoPublicId, ?string $imagePublicId): void
    {
        $uploadApi = new UploadApi();

        foreach ([['id' => $videoPublicId, 'type' => 'video'], ['id' => $imagePublicId, 'type' => 'image']] as $asset) {
            if (!$asset['id']) {
                continue;
            }

            try {
                $uploadApi->destroy($asset['id'], ['resource_type' => $asset['type']]);
            } catch (Throwable $e) {
                Log::warning('Failed to clean up Cloudinary asset after optimized upload failure', [
                    'video_id' => $this->videoId,
                    'public_id' => $asset['id'],
                    'resource_type' => $asset['type'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function notificationKeyForReason(string $reason): string
    {
        $normalizedReason = strtolower(trim(explode(':', $reason)[0]));

        $moderationReasons = [
            'toxic_text',
            'threat_text',
            'identity_hate_text',
            'obscene_text',
            'insult_text',
            'nsfw_image',
            'nsfw_frame',
            'clean',
            'empty',
        ];

        if (in_array($normalizedReason, $moderationReasons, true)) {
            return 'site.notification_video_upload_blocked';
        }

        return 'site.notification_video_upload_failed';
    }


    protected function deleteTempFiles(): void
    {
        try {
            Storage::disk('uploads_tmp')->delete($this->videoPath);
            Storage::disk('uploads_tmp')->delete($this->imagePath);
        } catch (Throwable $e) {
            Log::warning('Failed to delete optimized upload temp files', [
                'video_id' => $this->videoId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $video = Video::find($this->videoId);

        if ($video && $video->processed === UploadStateEnum::PROCESSING) {
            $video->update([
                'processed' => UploadStateEnum::FAILED->value,
                'processing_token' => null,
            ]);

            Notification::create([
                'user_id' => $video->user_id,
                'notification' => 'site.notification_video_upload_failed',
                'success' => false,
            ]);
        }

        $this->deleteTempFiles();

        Prometheus::counter('uploads_failed_total')->inc(['optimized']);

        Log::error('OptimizedSanitizeJob failed permanently', [
            'video_id' => $this->videoId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
