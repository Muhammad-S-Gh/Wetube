<?php

namespace App\Http\Middleware;

use Closure;
use Iamfarhad\Prometheus\Facades\Prometheus;

class TrackQueueWaitTime
{
    public function handle($job, Closure $next)
    {
        $dispatchedAt = $job->dispatched_at ?? microtime(true);
        $waitTime = microtime(true) - $dispatchedAt;

        Prometheus::histogram('queue_wait_seconds')
            ->observe(
                $waitTime,
                [
                    $job->pipeline ?? 'unknown',
                    class_basename($job)
                ]
            );
        return $next($job);
    }
}
