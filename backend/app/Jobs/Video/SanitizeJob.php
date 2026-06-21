<?php

namespace App\Jobs\Video;

use App\Enums\UploadStateEnum;
use App\Http\Middleware\TrackQueueWaitTime;
use App\Models\Notification;
use App\Models\Video;
use App\Services\ModerationService;
use Iamfarhad\Prometheus\Facades\Prometheus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SanitizeJob implements ShouldQueue
{
    use Queueable;
    public int $tries = 1;
    public int $timeout = 180;
    public bool $deleteWhenMissingModels = true;
    public $dispatched_at;
    public string $pipeline = 'ffmpeg';
    public function __construct(
        protected int $videoId,
        protected string $videoPath,
        protected string $imagePath,
        protected string $title,
    ) {
        $this->dispatched_at = microtime(true);
    }
    public function middleware(): array
    {
        return [new TrackQueueWaitTime];
    }

    public function handle(ModerationService $moderation): void
    {
        $video = Video::find($this->videoId);
        if (!$video || $video->processed !== UploadStateEnum::PROCESSING) {
            Log::info('Skipping moderation job because video is no longer pending processing', [
                'video_id' => $this->videoId,
            ]);
            return;
        }

        if (!Storage::disk('uploads_tmp')->exists($this->imagePath) || !Storage::disk('uploads_tmp')->exists($this->videoPath)) {
            $this->failUpload($video, 'asset', 'file_not_found');
            return;
        }

        $text = $moderation->moderateText($this->title);
        if ($text['pass'] === false) {
            $this->failUpload($video, 'Title', (string) $text['reason']);
            return;
        }

        $imageResult = $moderation->moderateImage($this->imagePath);
        if (!$imageResult['pass']) {
            $this->failUpload($video, 'thumbnail', (string) $imageResult['reason']);
            return;
        }

        $videoResult = $moderation->moderateVideo($this->videoPath);
        if (!$videoResult['pass']) {
            $this->failUpload($video, 'video', (string) $videoResult['reason']);
            return;
        }

        ConvertVideoJob::dispatch($this->videoId, $this->videoPath, $this->imagePath);
    }

    protected function failUpload(Video $video, string $assetType, string $reason): void
    {
        Log::warning('Moderation failed, marking video as blocked', [
            'video_id' => $video->id,
            'asset_type' => $assetType,
            'reason' => $reason,
        ]);

        $notificationKey = $this->notificationKeyForReason($reason);

        $video->update([
            'title' => 'BLOCKED',
            'processed' => UploadStateEnum::FAILED,
            'processing_token' => null
        ]);

        Prometheus::counter('uploads_failed_total')
            ->inc(['ffmpeg']);

        Notification::create([
            'user_id' => $video->user_id,
            'notification' => $notificationKey,
            'success' => false,
        ]);

        try {
            Storage::disk('uploads_tmp')->delete($this->videoPath);
            Storage::disk('uploads_tmp')->delete($this->imagePath);
        } catch (Throwable $e) {
            Log::warning('Failed to delete temp files', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ]);
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

    public function failed(?Throwable $exception): void
    {
        $video = Video::find($this->videoId);
        if ($video) {
            $video->update([
                'processed'        => UploadStateEnum::FAILED->value,
                'processing_token' => null,
            ]);

            Notification::create([
                'user_id'      => $video->user_id,
                'notification' => 'site.notification_video_upload_failed',
                'success'      => false,
            ]);
        }

        Log::error('SanitizeJob failed permanently', [
            'video_id'   => $this->videoId,
            'error'      => $exception?->getMessage(),
        ]);

        Prometheus::counter('uploads_failed_total')->inc(['ffmpeg']);

        try {
            if ($this->videoPath) {
                Storage::disk('uploads_tmp')->delete($this->videoPath);
            }
            if ($this->imagePath) {
                Storage::disk('uploads_tmp')->delete($this->imagePath);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to delete temp files in SanitizeJob failed()', [
                'video_id' => $this->videoId,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
