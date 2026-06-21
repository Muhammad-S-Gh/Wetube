<?php

namespace App\Console\Commands\Video;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old temporary uploaded and converted files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->cleanupDisk('uploads_tmp', 120);
        $this->cleanupDisk('converted_tmp', 120);

        $this->info('Temporary files cleaned successfully.');

        return Command::SUCCESS;
    }

    private function cleanupDisk(string $disk, int $minutes): void
    {
        foreach (Storage::disk($disk)->allFiles() as $file) {
            try {
                $lastModified = Storage::disk($disk)->lastModified($file);

                if (Carbon::createFromTimestamp($lastModified)->lt(now()->subMinutes($minutes))) {
                    Storage::disk($disk)->delete($file);
                    $this->info("Deleted: {$file}");
                }
            } catch (\Throwable $e) {
                Log::error('Cleanup failed', [
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
