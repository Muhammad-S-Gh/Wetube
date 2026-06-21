<?php

namespace App\Jobs\Video;

use App\Http\Middleware\TrackQueueWaitTime;
use App\Models\Media;
use App\Models\Video;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Str;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use Iamfarhad\Prometheus\Facades\Prometheus;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessVideoJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, Batchable;

    public int $tries = 1;
    public int $timeout = 1800;
    public bool $deleteWhenMissingModels = true;
    public $dispatched_at;
    public string $pipeline = 'ffmpeg';

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $videoId,
        protected string $videoPath,
        protected int $width,
        protected int $height,
        protected int $bitrate,
        protected string $format,
        protected string $token
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
        $video = Video::find($this->videoId);

        if (!$video || $video->processing_token !== $this->token) {
            Log::info('Skipping stale process video job', [
                'video_id' => $this->videoId,
                'job_token' => $this->token,
                'current_token' => $video?->processing_token,
            ]);
            return; // stale job, ignore
        }

        /*
        |
        | setAdditionalParameters([
        | '-preset', 'veryfast',
        | // Controls Speed & File Size after encoding(compression) (ultrafast, veryfast, medium, slow)
        | '-threads', '2'
        | // If '0' FFMpeg decide the optimal threading and that is not production safe coz If there is like 4 queue workers running
        | // and in each one FFMpeg set 4 threads = 16 threads in use (CPU Thrashing) so i will limit FFMpeg threading to 2 and queue workers
        | // must be limited to 2 to follow the rule (workers × threads_per_job ≤ CPU cores)
        | ]),
        |
        | ->setAudioCodec('')
        | // choose the right Audio format for MP4 and Webm
        |
        */

        if ($this->batch()?->cancelled()) {
            return;
        }

        // ------------------------
        // Encode
        // ------------------------

        $formatObj = match ($this->format) {
            'mp4' => (new X264())
                ->setKiloBitrate($this->bitrate)
                ->setAudioCodec('aac'),

            'webm' => (new WebM())
                ->setKiloBitrate($this->bitrate)
                ->setAudioCodec('libvorbis'),
        };

        $formatObj->setAdditionalParameters([
            '-preset',
            'veryfast',
            '-threads',
            config('laravel-ffmpeg.threads')
        ]);

        $fileName = Str::uuid() . '.' . $this->format;

        FFMpeg::fromDisk('uploads_tmp')
            ->open($this->videoPath)
            ->export()
            ->toDisk('converted_tmp')
            ->inFormat($formatObj)
            // ResizeFilter::RESIZEMODE_INSET will detect the Portrait from Landscape
            // Without it the Exact resolution is Forced and it may cause: Stretch video & distort aspect ratio
            ->addFilter(function ($filters) {
                $filters->resize(
                    new Dimension($this->width, $this->height),
                    ResizeFilter::RESIZEMODE_INSET
                );
            })
            ->save($fileName);

        // ------------------------
        // Upload (same job)
        // ------------------------

        $path = Storage::disk('converted_tmp')->path($fileName);
        $publicId = "videos/{$this->videoId}/{$this->token}/{$this->height}p_{$this->format}";

        try {
            $video->refresh();
            if ($video->processing_token !== $this->token) {
                Log::info('Skipping stale process video job before upload', [
                    'video_id' => $this->videoId,
                    'job_token' => $this->token,
                    'current_token' => $video->processing_token,
                ]);
                return;
            }

            $cloudinaryVideo = (new UploadApi())->unsignedUpload(
                $path,
                config('filesystems.disks.cloudinary.videos'),
                [
                    'resource_type'  => 'video',
                    'public_id'      => $publicId,
                ]
            );

            $requiredKeys = ['public_id', 'secure_url', 'resource_type', 'format'];
            foreach ($requiredKeys as $key) {
                if (!isset($cloudinaryVideo[$key]) || $cloudinaryVideo[$key] === '') {
                    throw new \RuntimeException("Cloudinary upload response missing key: {$key}");
                }
            }

            $video->refresh();
            if ($video->processing_token !== $this->token) {
                (new UploadApi())->destroy($cloudinaryVideo['public_id'], ['resource_type' => 'video']);

                Log::info('Skipping stale process video job after upload', [
                    'video_id' => $this->videoId,
                    'job_token' => $this->token,
                    'current_token' => $video->processing_token,
                    'public_id' => $cloudinaryVideo['public_id'],
                ]);
                return;
            }

            Media::create([
                'mediable_type'   => Video::class,
                'mediable_id'     => $this->videoId,
                'disk'            => config('filesystems.disks.cloudinary.driver'),
                'path'            => $cloudinaryVideo['secure_url'],
                'public_id'       => $cloudinaryVideo['public_id'],
                'resource_type'   => $cloudinaryVideo['resource_type'],
                'format'          => $cloudinaryVideo['format'],
                'quality'         => min($this->height, $this->width),
                'cloud_response'  => json_encode($cloudinaryVideo)
            ]);

            Log::info('Video variant uploaded successfully', [
                'video_id' => $this->videoId,
                'format' => $this->format,
                'resolution' => $this->height . 'p',
                'public_id' => $cloudinaryVideo['public_id'],
            ]);
        } catch (Throwable $e) {

            if (isset($cloudinaryVideo['public_id'])) {
                (new UploadApi())->destroy($cloudinaryVideo['public_id'], ['resource_type' => 'video']);
            }

            Log::error('Upload failed', [
                'video_id' => $this->videoId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        } finally {
            Storage::disk('converted_tmp')->delete($fileName);
        }
    }

    public function failed(?Throwable $exception)
    {
        Prometheus::counter('uploads_failed_total')->inc(['ffmpeg']);

        Log::error("ConvertVideoForStreaming job failed for video ID {$this->videoId}", [
            'exception'   => $exception,
            'video_id'    => $this->videoId,
            'video_path'  => $this->videoPath,
            'error'       => $exception->getMessage()
        ]);
    }
}
