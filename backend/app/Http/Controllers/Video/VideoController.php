<?php

namespace App\Http\Controllers\Video;

use App\Enums\UploadStateEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Video\OptimizedUploadRequest;
use App\Http\Requests\Video\ReportVideoRequest;
use App\Http\Requests\Video\StoreCommentRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Http\Requests\Video\UploadRequest;
use App\Models\Notification;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        protected VideoService $videoService
    ) {}

    /**
     * Display list of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $videos = $this->videoService->index($user);
        $hasProcessing = $this->videoService->hasProcessingVideo($user);

        return view('videos.index', [
            'videos' => $videos,
            'hasProcessingVideo' => $hasProcessing,
        ]);
    }

    /**
     * Display creating resource page.
     */
    public function create()
    {
        return view('videos.uploader');
    }

    /**
     * Store the specified resource.
     */
    public function store(UploadRequest $request)
    {
        /** @var \App\Models\Video $video */
        $video = $this->videoService->uploadVideo($request->validated());
        return back()->with([
            'success' => __('site.process_video_upload'),
            'video_id' => $video->id
        ]);
    }

    /**
     * Optimized upload: moderation first, then async Cloudinary streaming via queue jobs.
     */
    public function optimizedStore(OptimizedUploadRequest $request)
    {
        $video = $this->videoService->uploadVideoOptimized($request->validated());

        return back()->with([
            'success' => __('site.process_video_upload'),
            'video_id' => $video->id,
        ]);
    }

    /**
     * Display the specified resource.
     */

    public function show(Request $request, Video $video)
    {
        $user = $request->user();

        if ($user?->is_admin) {
            return redirect()->route('admin.videos.show', $video);
        }

        abort_if(
            $video->processed !== UploadStateEnum::COMPLETED && (!$user || $user->id !== $video->user_id),
            403
        );

        return view('videos.show', $this->videoService->showVideo($user, $video));
    }

    /**
     * Add a comment from an authenticated viewer.
     */
    public function storeComment(StoreCommentRequest $request, Video $video)
    {
        $this->videoService->storeComment($request->user(), $video, $request->validated()['body']);

        return back()->with('success', __('site.comment_added_successfully'));
    }

    /**
     * Toggle video like for authenticated viewer.
     */
    public function toggleLike(Request $request, Video $video)
    {
        $this->videoService->toggleLike($request->user(), $video);
        return back()->with('success', __('site.video_liked_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoRequest $request, Video $video)
    {
        $this->videoService->updateVideo($request->user(), $video, $request->validated());

        return back()->with('success', __('site.video_updated_processing'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Video $video)
    {
        $this->videoService->deleteVideo($request->user(), $video);

        return back()->with('success', __('site.video_deleted_successfully'));
    }

    public function checkUploadStatus(Video $video)
    {
        return response()->json([
            'processed' => $video->processed->value,
        ]);
    }

    /**
     * Return current user notifications for frontend polling/use.
     */
    public function notifications(Request $request)
    {
        $limit = max(1, min((int) $request->query('limit', 20), 50));

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit($limit)
            ->get(['id', 'notification', 'success', 'created_at']);

        return response()->json([
            'count' => $request->user()->notifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Remove one navbar notification.
     */
    public function destroyNotification(Request $request, Notification $notification)
    {
        abort_unless($request->user() && $notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return back()->with('success', __('site.notification_deleted_successfully'));
    }

    /**
     * Report a video (create an alert).
     */
    public function report(ReportVideoRequest $request, Video $video)
    {
        $alert = $this->videoService->reportVideo($request->user(), $video, $request->validated());

        $this->videoService->notifyAdminsOfReport($alert);

        return back()->with('success', __('site.report_submitted_successfully'));
    }
}
