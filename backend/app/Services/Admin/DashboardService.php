<?php

namespace App\Services\Admin;

use App\Actions\Jetstream\DeleteUser as DeleteUserAction;
use App\Enums\UploadStateEnum;
use App\Models\Alert;
use App\Models\History;
use App\Models\Notification;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Media;
use App\Models\User;
use App\Models\Video;
use App\Services\CloudinaryMediaService;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DashboardService
{
    public function __construct(
        protected DeleteUserAction $deleteUserAction
    ) {}

    // ============================================ Dashboard
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getStats(),
            'chartData' => $this->getChartData(),
            'activities' => $this->getRecentActivities(),
            'pendingAlertsCount' => $this->getPendingAlertsCount(),
        ];
    }

    private function getVideoStats(): array
    {
        $stats = Video::selectRaw('
        COUNT(*) as total,
        SUM(processed = ?) as completed,
        SUM(processed = ?) as processing,
        SUM(processed = ?) as failed,
        SUM(created_at >= ?) as this_month
    ', [
            UploadStateEnum::COMPLETED->value,
            UploadStateEnum::PROCESSING->value,
            UploadStateEnum::FAILED->value,
            now()->subMonth()
        ])->first();

        return [
            'total' => $stats->total,
            'completed' => $stats->completed,
            'processing' => $stats->processing,
            'failed' => $stats->failed,
            'this_month' => $stats->this_month,
        ];
    }

    private function getUserStats(): array
    {
        return User::selectRaw('
        COUNT(CASE WHEN is_admin = 0 THEN 1 END) as total_users,
        COUNT(CASE WHEN is_admin = 0 AND created_at >= ? THEN 1 END) as new_users,
        SUM(is_admin = 1) as admin_users
    ', [now()->subMonth()])->first()->toArray();
    }

    private function getEngagementStats(): array
    {
        return Like::selectRaw('
        SUM(`like` = 1) as likes,
        SUM(`like` = 0) as dislikes
    ')->first()->toArray();
    }

    private function getStats(): array
    {
        return Cache::remember('dashboard.stats', now()->addMinutes(5), function () {

            $user = $this->getUserStats();
            $video = $this->getVideoStats();
            $engagement = $this->getEngagementStats();

            return [
                'total_users' => $user['total_users'],
                'new_users_this_month' => $user['new_users'],
                'admin_users' => $user['admin_users'],

                'total_videos' => $video['total'],
                'completed_videos' => $video['completed'],
                'processing_videos' => $video['processing'],
                'failed_videos' => $video['failed'],

                'total_views' => History::count(),
                'total_comments' => Comment::count(),

                'total_likes' => $engagement['likes'],

                'total_alerts' => Alert::count(),
                'average_video_size_mb' => $this->getAverageVideoSize(),
            ];
        });
    }

    private function getAverageVideoSize(): float
    {
        $avgSize = Media::where('resource_type', 'video')
            ->selectRaw('AVG(CAST(JSON_EXTRACT(cloud_response, "$.bytes") AS UNSIGNED)) as avg_size')
            ->value('avg_size');

        return $avgSize ? round($avgSize / (1024 * 1024), 2) : 0;
    }

    private function getChartData(): array
    {
        return Cache::remember('dashboard.charts', now()->addMinutes(15), function () {
            return $this->buildChartData();
        });
    }

    private function buildChartData(): array
    {
        $last30Days = collect(range(0, 29))->mapWithKeys(function ($day) {
            $date = now()->subDays(29 - $day)->format('Y-m-d');
            return [$date => 0];
        });

        $usersPerDay = User::where('created_at', '>=', now()->subDays(30))
            ->where('is_admin', 0)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->union($last30Days) // merges with $last30Days
            ->sortKeys();

        $videosPerDay = Video::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->union($last30Days)
            ->sortKeys();

        $viewsPerDay = History::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->union($last30Days)
            ->sortKeys();

        $videoStats = $this->getVideoStats();
        $likeStats = $this->getEngagementStats();


        return [
            'usersPerDay' => [
                'labels' => $usersPerDay->keys()->toArray(),
                'data' => $usersPerDay->values()->toArray(),
            ],
            'videosPerDay' => [
                'labels' => $videosPerDay->keys()->toArray(),
                'data' => $videosPerDay->values()->toArray(),
            ],
            'viewsPerDay' => [
                'labels' => $viewsPerDay->keys()->toArray(),
                'data' => $viewsPerDay->values()->toArray(),
            ],
            'videoStatus' => [
                'labels' => [
                    __('site.status_completed'),
                    __('site.status_processing'),
                    __('site.status_failed'),
                ],
                'data' => [
                    $videoStats['completed'],
                    $videoStats['processing'],
                    $videoStats['failed'],
                ],
            ],
            'engagement' => [
                'labels' => [
                    __('site.likes'),
                    __('site.admin_dislikes'),
                ],
                'data' => [
                    $likeStats['likes'],
                    $likeStats['dislikes'],
                ],
            ],
        ];
    }

    private function getRecentActivities(): array
    {
        return Cache::remember('dashboard.activities', now()->addMinutes(2), function () {
            return [
                'recent_videos' => Video::with('user')
                    ->latest()
                    ->limit(5)
                    ->get(),
                'recent_users' => User::where('is_admin', 0)
                    ->latest()
                    ->limit(5)
                    ->get(),
                'recent_alerts' => Alert::with('user')
                    ->where('resolved', false)
                    ->latest()
                    ->limit(5)
                    ->get(),
            ];
        });
    }

    public function getPendingAlertsCount(): int
    {
        return Cache::remember('dashboard.alerts_count', now()->addMinutes(1), function () {
            return Alert::where('resolved', false)->count();
        });
    }

    // ============================================ Actions

    public function approveAlert(Alert $alert): RedirectResponse
    {
        $alert->update(['resolved' => true]);
        return back()->with('success', __('site.admin_alert_resolved_success'));
    }

    public function getUsersData(string $search, ?int $selectedUserId): array
    {
        $videoCounts = [
            'videos as total_videos',
            'videos as completed_videos' => fn($q) => $q->where('processed', UploadStateEnum::COMPLETED->value),
            'videos as failed_videos' => fn($q) => $q->where('processed', UploadStateEnum::FAILED->value),
        ];

        $users = User::withCount($videoCounts)
            ->where('is_admin', 0)
            ->search($search)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $selectedUser = null;
        $selectedUserVideos = null;

        if ($selectedUserId) {
            $selectedUser = User::withCount($videoCounts)->find($selectedUserId);

            if ($selectedUser) {
                $selectedUserVideos = Video::with(['media'])
                    ->where('user_id', $selectedUser->id)
                    ->latest()
                    ->paginate(config('services.pagination.videos'), ['*'], 'selected_videos_page')
                    ->withQueryString();
            }
        }

        return [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'selectedUserVideos' => $selectedUserVideos,
        ];
    }

    public function getVideosData(string $search)
    {
        return Video::with(['user', 'media'])
            ->search($search)
            ->latest()
            ->paginate(config('services.pagination.videos'))
            ->withQueryString();
    }

    public function getAlertsData()
    {
        return Alert::with(['user', 'video.user', 'video.media'])
            ->where('resolved', false)
            ->latest()
            ->paginate(config('services.pagination.videos'));
    }

    public function getVideoReviewData(Video $video): array
    {
        $video->load([
            'user',
            'media',
        ])->loadCount([
            'viewers',
            'comments',
            'likes as likes_count' => fn($query) => $query->where('like', true),
        ]);

        $alerts = Alert::with('user')
            ->where('video_id', $video->id)
            ->where('resolved', false)
            ->latest()
            ->get();

        $variantMap = app(CloudinaryMediaService::class)->buildVariantMap($video);

        $thumbnail = $video->media->firstWhere('resource_type', 'image');

        return [
            'video' => $video,
            'alerts' => $alerts,
            'variantMap' => $variantMap,
            'formatOptions' => array_keys($variantMap),
            'thumbnail' => $thumbnail,
            'thumbnailUrl' => $thumbnail
                ? app(CloudinaryMediaService::class)->imageDisplayUrl($thumbnail)
                : null,
        ];
    }

    // Block/unblock service methods removed

    public function deleteVideo(Video $video): RedirectResponse
    {
        $video->loadMissing('media');

        // Notify the video owner that their video was deleted by an admin
        Notification::query()->create([
            'user_id' => $video->user_id,
            'notification' => 'site.notification_video_deleted_by_admin',
            'success' => false,
        ]);

        foreach ($video->media as $media) {
            try {
                (new UploadApi())->destroy($media->public_id, ['resource_type' => $media->resource_type]);
            } catch (\Throwable $e) {
                Log::error('Failed to delete media from Cloudinary during admin video delete', [
                    'video_id' => $video->id,
                    'media_id' => $media->id,
                    'public_id' => $media->public_id,
                    'resource_type' => $media->resource_type,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'video' => __('site.video_delete_failed'),
                ]);
            }
        }

        DB::transaction(function () use ($video) {
            Media::query()
                ->where('mediable_type', Video::class)
                ->where('mediable_id', $video->id)
                ->delete();

            $video->delete();
        });

        return redirect()->route('admin.videos')->with('success', __('site.admin_video_deleted_success'));
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()?->id === $user->id, 403, __('site.admin_cannot_delete_self'));

        $this->deleteUserAction->delete($user);

        return back()->with('success', __('site.admin_user_deleted_success', ['name' => $user->name]));
    }
}
