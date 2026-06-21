<?php

namespace App\Jobs\Video;

use App\Enums\UploadStateEnum;
use App\Http\Middleware\TrackQueueWaitTime;
use App\Models\Notification;
use App\Models\Video;
use Cloudinary\Api\Admin\AdminApi;
use Iamfarhad\Prometheus\Facades\Prometheus;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Throwable;

class ConvertVideoJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, Batchable;

    /**
     * Create a new job instance.
     */

    public int $tries = 1;
    public int $timeout = 120;
    public bool $deleteWhenMissingModels = true;
    public $dispatched_at;
    public string $pipeline = 'ffmpeg';

    public function __construct(
        protected int $videoId,
        protected string $videoPath,
        protected string $imagePath
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
        // Original Video
        $video = Video::findOrFail($this->videoId);

        // Decode Original Video via FFMpeg
        $media = FFMpeg::fromDisk('uploads_tmp')->open($this->videoPath);

        // Fetching Dimensions
        $dimensions = $media->getVideoStream()->getDimensions();
        $width = $dimensions->getWidth();
        $height = $dimensions->getHeight();

        // Fetching Duration
        $durationInSeconds = $media->getDurationInSeconds();

        // Update video info
        $token = (string) Str::uuid();
        $video->update([
            'hours' => floor($durationInSeconds / 3600),
            'minutes' => floor(($durationInSeconds / 60) % 60),
            'seconds' => $durationInSeconds % 60,
            'longitudinal' => $height > $width,
            'processing_token' => $token
        ]);

        // Dispatch Encoding jobs
        $resolutions = [];

        if ($height >= 480) {
            $resolutions[] = ['w' => 854, 'h' => 480, 'bitrate' => 1250];
        }

        if ($height >= 240) {
            $resolutions[] = ['w' => 426, 'h' => 240, 'bitrate' => 450];
        }

        if (empty($resolutions)) {
            $resolutions[] = [
                'w' => $width,
                'h' => $height,
                'bitrate' => 300 // sufficient for sub‑240p source videos
            ];
        }

        $jobs = [];
        foreach ($resolutions as $res) {
            foreach (['mp4', 'webm'] as $format) {
                $jobs[] = new ProcessVideoJob(
                    $this->videoId,
                    $this->videoPath,
                    $res['w'],
                    $res['h'],
                    $res['bitrate'],
                    $format,
                    $token
                );
            }
        }

        $videoId = $this->videoId;
        $videoPath = $this->videoPath;
        $imagePath = $this->imagePath;

        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($videoId, $videoPath, $imagePath, $token) {
                FinalizeUploadJob::dispatch($videoId, $videoPath, $imagePath, $token);
            })
            ->catch(function (Batch $batch, Throwable $e) use ($videoId, $token) {
                Prometheus::counter('uploads_failed_total')->inc(['ffmpeg']);

                Log::error('Batch failed', [
                    'batch_id' => $batch->id,
                    'video_id' => $videoId,
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);

                try {
                    $admin = new AdminApi();
                    $prefix = "videos/{$videoId}/{$token}";
                    $options = [
                        'resource_type' => 'video',
                        'type' => 'upload',
                    ];

                    // Cloudinary may paginate deletions for large prefix sets.
                    do {
                        $result = $admin->deleteAssetsByPrefix($prefix, $options);
                        $nextCursor = $result['next_cursor'] ?? null;

                        if ($nextCursor) {
                            $options['next_cursor'] = $nextCursor;
                        } else {
                            unset($options['next_cursor']);
                        }
                    } while ($nextCursor);
                } catch (Throwable $cleanupException) {
                    Log::warning('Cloudinary cleanup failed after batch failure', [
                        'video_id' => $videoId,
                        'token' => $token,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }

                Video::whereKey($videoId)->update([
                    'processed' => UploadStateEnum::FAILED->value,
                    'processing_token' => null
                ]);

                $video = Video::query()->find($videoId);
                if ($video) {
                    Notification::query()->create([
                        'user_id' => $video->user_id,
                        'notification' => 'site.notification_video_upload_failed',
                        'success' => false,
                    ]);
                }
            })

            ->name('video-processing-' . $videoId)
            ->dispatch();
    }

    public function failed(?Throwable $exception)
    {
        Prometheus::counter('uploads_failed_total')->inc(['ffmpeg']);

        Log::error("ConvertVideoForStreaming job failed for video ID {$this->videoId}", [
            'exception'   => $exception,
            'video_id'    => $this->videoId,
            'video_path'  => $this->videoPath,
            'image_path'  => $this->imagePath,
            'error'       => $exception->getMessage()
        ]);
    }
}
