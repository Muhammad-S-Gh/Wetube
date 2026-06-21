<?php

namespace App\Console\Commands\Video;

use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteBlockedVideosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:delete-blocked';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Delete all blocked videos and their associated media from Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting deletion of blocked videos...');

        $blockedVideos = Video::where('title', 'BLOCKED')->get();

        if ($blockedVideos->isEmpty()) {
            $this->info('No blocked videos found.');
            return 0;
        }

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($blockedVideos as $video) {
            try {
                DB::transaction(function () use ($video) {
                    $video->delete();
                });

                $deletedCount++;
                $this->line("✓ Deleted blocked video: {$video->id}");
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error('Failed to delete blocked video', [
                    'video_id' => $video->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("✗ Failed to delete video {$video->id}: {$e->getMessage()}");
            }
        }

        $this->info("Blocked videos deletion completed. Deleted: {$deletedCount}, Failed: {$failedCount}");

        return $deletedCount > 0 || $failedCount === 0 ? 0 : 1;
    }
}
