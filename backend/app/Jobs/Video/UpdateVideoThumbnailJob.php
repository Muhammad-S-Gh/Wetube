<?php

namespace App\Jobs\Video;

use App\Models\Media;
use App\Models\Notification;
use App\Models\Video;
use App\Services\ModerationService;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Throwable;

class UpdateVideoThumbnailJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    public int $tries = 3;
    public int $timeout = 300;
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        protected int $videoId,
        protected string $tempImagePath,
        protected ?string $oldPublicId
    ) {}

    public function handle(ModerationService $moderation): void
    {
        $video = Video::find($this->videoId);
        if (!$video) {
            return;
        }

        if (!Storage::disk('uploads_tmp')->exists($this->tempImagePath)) {
            Log::error('UpdateVideoThumbnailJob: temp file missing', [
                'video_id' => $this->videoId,
                'path'     => $this->tempImagePath,
            ]);
            return;
        }

        $absolutePath = Storage::disk('uploads_tmp')->path($this->tempImagePath);

        try {
            $modResult = $moderation->moderateImage($this->tempImagePath);

            if (!($modResult['pass'] ?? false)) {
                if (str_starts_with($modResult['reason'] ?? '', 'model_loading')) {
                    Log::info('Thumbnail moderation: model still loading, releasing job', [
                        'video_id' => $this->videoId,
                        'reason'   => $modResult['reason'],
                    ]);
                    $this->release(30);
                    return;
                }

                Log::info('Thumbnail moderation rejected', [
                    'video_id' => $this->videoId,
                    'reason'   => $modResult['reason'],
                ]);

                Notification::create([
                    'user_id'      => $video->user_id,
                    'notification' => 'site.notification_thumbnail_rejected',
                    'success'      => false,
                ]);
                return;
            }

            $resized = ImageManager::imagick()
                ->read($absolutePath)
                ->resize(320, 180);

            $resizedPath = tempnam(sys_get_temp_dir(), 'resized_') . '.jpg';
            $resized->encode()->save($resizedPath);

            $uploadResult = (new UploadApi())->unsignedUpload(
                $resizedPath,
                config('filesystems.disks.cloudinary.images'),
                ['resource_type' => 'image']
            );

            DB::transaction(function () use ($video, $uploadResult) {
                $video->media()->where('resource_type', 'image')->delete();

                Media::create([
                    'mediable_type'  => Video::class,
                    'mediable_id'    => $video->id,
                    'disk'           => config('filesystems.disks.cloudinary.driver'),
                    'path'           => $uploadResult['secure_url'],
                    'public_id'      => $uploadResult['public_id'],
                    'resource_type'  => $uploadResult['resource_type'],
                    'format'         => $uploadResult['format'],
                    'quality'        => ($uploadResult['width'] ?? '') . 'x' . ($uploadResult['height'] ?? ''),
                    'cloud_response' => json_encode($uploadResult),
                ]);
            });

            if ($this->oldPublicId) {
                try {
                    (new UploadApi())->destroy($this->oldPublicId, ['resource_type' => 'image']);
                } catch (Throwable $e) {
                    Log::warning('Failed to delete old thumbnail', [
                        'video_id'  => $this->videoId,
                        'public_id' => $this->oldPublicId,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            Notification::create([
                'user_id'      => $video->user_id,
                'notification' => 'site.video_updated_successfully',
                'success'      => true,
            ]);

            Log::info('Video thumbnail updated', [
                'video_id'  => $this->videoId,
                'public_id' => $uploadResult['public_id'],
            ]);
        } catch (Throwable $e) {
            Log::error('UpdateVideoThumbnailJob failed', [
                'video_id' => $this->videoId,
                'error'    => $e->getMessage(),
            ]);

            if (isset($uploadResult['public_id'])) {
                try {
                    (new UploadApi())->destroy($uploadResult['public_id'], ['resource_type' => 'image']);
                } catch (Throwable $cleanupException) {
                    Log::warning('Failed to clean up Cloudinary after failure', [
                        'public_id' => $uploadResult['public_id'],
                        'error'     => $cleanupException->getMessage(),
                    ]);
                }
            }

            throw $e;
        } finally {
            Storage::disk('uploads_tmp')->delete($this->tempImagePath);
            if (isset($resizedPath) && file_exists($resizedPath)) {
                unlink($resizedPath);
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('UpdateVideoThumbnailJob exhausted retries', [
            'video_id' => $this->videoId,
            'error'    => $exception?->getMessage(),
        ]);

        Storage::disk('uploads_tmp')->delete($this->tempImagePath);
    }
}
