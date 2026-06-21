<?php

namespace App\Console\Commands\Video;

use Illuminate\Console\Command;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupFailedVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unprocessed videos older than 60 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = now()->subMinutes(60);

        $videos = Video::where('processed', false)
            ->where('created_at', '<', $threshold)
            ->get();

        foreach ($videos as $video) {
            try {
                $videoId = $video->id;

                // Optional: delete related media if exists
                $video->media()->delete();

                // Delete video record
                $video->delete();

                $this->info("Deleted unprocessed video ID: {$videoId}");
            } catch (Throwable $e) {
                Log::error('Failed deleting unprocessed video', [
                    'video_id' => $video->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
