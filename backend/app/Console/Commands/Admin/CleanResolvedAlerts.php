<?php

namespace App\Console\Commands\Admin;

use App\Models\Alert;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanResolvedAlerts extends Command
{
    protected $signature = 'alerts:clean-resolved';

    protected $description = 'Deletes resolved alerts older than one month';

    public function handle(): int
    {
        $threshold = Carbon::now()->subMonth();

        $deleted = Alert::where('resolved', true)
            ->where('updated_at', '<', $threshold)
            ->delete();

        $this->info("Deleted {$deleted} resolved alerts older than one month.");

        return Command::SUCCESS;
    }
}
