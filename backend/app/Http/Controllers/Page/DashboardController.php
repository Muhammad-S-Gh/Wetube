<?php

namespace App\Http\Controllers\Page;

use App\Enums\UploadStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        $userVideos = Video::query()->where('user_id', $user->id);

        $stats = (clone $userVideos)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN processed = ? THEN 1 ELSE 0 END), 0) as processing',
                [UploadStateEnum::PROCESSING->value]
            )->selectRaw(
                'COALESCE(SUM(CASE WHEN processed = ? THEN 1 ELSE 0 END), 0) as completed',
                [UploadStateEnum::COMPLETED->value]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN processed = ? THEN 1 ELSE 0 END), 0) as failed',
                [UploadStateEnum::FAILED->value]
            )->first()
            ->toArray();

        $recentVideos = (clone $userVideos)
            ->with('media')
            ->latest('id')
            ->limit(4)
            ->get();

        return view('pages.dashboard', [
            'stats' => $stats,
            'recentVideos' => $recentVideos,
        ]);
    }
}
