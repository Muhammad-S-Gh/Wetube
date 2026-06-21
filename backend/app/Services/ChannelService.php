<?php

namespace App\Services;

use App\Enums\UploadStateEnum;
use App\Models\User;
use App\Models\Video;

class ChannelService
{
    public function getChannels($search = '')
    {
        $search = trim((string) $search);

        return User::query()
            ->where('is_admin', false)
            ->search($search)
            ->withCount([
                'videos as videos_count' => fn($query) => $query->where('processed', UploadStateEnum::COMPLETED->value),
            ])
            ->with([
                'videos' => fn($query) => $query
                    ->where('processed', UploadStateEnum::COMPLETED->value)
                    ->latest('id')
                    ->limit(1)
                    ->with('media'),
            ])
            ->orderByDesc('videos_count')
            ->paginate(config('services.pagination.videos'))
            ->withQueryString();
    }

    public function getChannelPageData(User $channel): array
    {
        abort_if($channel->is_admin, 404);

        $videos = Video::query()
            ->where('user_id', $channel->id)
            ->where('processed', UploadStateEnum::COMPLETED->value)
            ->with('media')
            ->latest('id')
            ->paginate(config('services.pagination.videos'));

        return [
            'user' => $channel,
            'videos' => $videos,
        ];
    }
}
