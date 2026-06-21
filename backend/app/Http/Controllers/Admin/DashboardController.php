<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\User;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Alert;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index()
    {
        $dashboardData = $this->dashboardService->getDashboardData();

        return view('admin.dashboard', [
            'stats' => $dashboardData['stats'],
            'chartData' => $dashboardData['chartData'],
            'activities' => $dashboardData['activities'],
            'pendingAlertsCount' => $dashboardData['pendingAlertsCount'],
        ]);
    }

    public function users(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $selectedUserId = $request->integer('user');

        $userData = $this->dashboardService->getUsersData($search, $selectedUserId);

        return view('admin.users', [
            'users' => $userData['users'],
            'search' => $search,
            'selectedUser' => $userData['selectedUser'],
            'selectedUserVideos' => $userData['selectedUserVideos'],
            'pendingAlertsCount' => $this->dashboardService->getPendingAlertsCount(),
        ]);
    }

    public function videos()
    {
        $search = trim((string) request()->query('search', ''));
        $videos = $this->dashboardService->getVideosData($search);

        return view('admin.videos', [
            'videos' => $videos,
            'search' => $search,
            'pendingAlertsCount' => $this->dashboardService->getPendingAlertsCount(),
        ]);
    }

    public function alerts()
    {
        $alerts = $this->dashboardService->getAlertsData();

        return view('admin.alerts', [
            'alerts' => $alerts,
            'pendingAlertsCount' => $this->dashboardService->getPendingAlertsCount(),
        ]);
    }

    public function show(Video $video): View
    {
        $videoData = $this->dashboardService->getVideoReviewData($video);

        return view('admin.videos.show', [
            ...$videoData,
            'pendingAlertsCount' => $this->dashboardService->getPendingAlertsCount(),
        ]);
    }

    // Block/unblock admin actions removed

    public function deleteVideo(Video $video)
    {
        return $this->dashboardService->deleteVideo($video);
    }

    public function approveAlert(Alert $alert)
    {
        return $this->dashboardService->approveAlert($alert);
    }

    public function deleteUser(Request $request, User $user)
    {
        return $this->dashboardService->deleteUser($request, $user);
    }
}
