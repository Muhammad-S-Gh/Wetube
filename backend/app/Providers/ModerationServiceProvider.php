<?php

namespace App\Providers;

use App\Services\ModerationService;
use Illuminate\Support\ServiceProvider;

class ModerationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ModerationService::class, function ($app) {
            return new ModerationService(
                config('services.hugging_face.key'),
                config('services.hugging_face.base_url'),
                config('services.hugging_face.timeout'),
                config('services.hugging_face.text_toxic_threshold', 0.85),
                config('services.hugging_face.text_threat_threshold', 0.85),
                config('services.hugging_face.text_identity_hate_threshold', 0.85),
                config('services.hugging_face.text_obscene_threshold', 0.85),
                config('services.hugging_face.text_insult_threshold', 0.85),
                config('services.hugging_face.image_nsfw_threshold', 0.35)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
