<?php

namespace App\Services;

use App\Enums\UploadStateEnum;
use App\Jobs\Video\OptimizedUploadJob;
use App\Jobs\Video\SanitizeJob;
use App\Jobs\Video\UpdateVideoThumbnailJob;
use App\Models\Alert;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Media;
use App\Models\Notification;
use App\Models\User;
use App\Models\Video;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\ModerationService;
use Iamfarhad\Prometheus\Facades\Prometheus;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Class VideoService.
 */
class VideoService
{
    public function __construct(
        protected ModerationService $moderationService,
        protected CloudinaryMediaService $cloudinaryMediaService,
    ) {}

    public function index(User $user)
    {
        return Video::query()
            ->where('user_id', $user->id)
            ->with([
                'media',
            ])
            ->latest('id')
            ->paginate(config('services.pagination.videos'))
            ->withQueryString();
    }

    public function showVideo(?User $user, Video $video): array
    {
        $video->load([
            'media',
            'user:id,name,profile_photo_path',
        ])->loadCount([
            'viewers',
            'comments',
            'likes as likes_count' => fn($query) => $query->where('like', true),
        ]);

        $comments = $video->comments()
            ->with('user:id,name,profile_photo_path')
            ->latest('id')
            ->paginate(config('services.pagination.videos'))
            ->withQueryString();

        $variantMap = $this->cloudinaryMediaService->buildVariantMap($video);

        if (!empty($variantMap) && $user && $user->id !== $video->user_id) {
            $user->videoInHistory()->syncWithoutDetaching([
                $video->id => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        return [
            'video' => $video,
            'variantMap' => $variantMap,
            'formatOptions' => array_keys($variantMap),
            'comments' => $comments,
            'likedByViewer' => $user
                ? $video->likes()
                ->where('user_id', $user->id)
                ->where('like', true)
                ->exists()
                : false,
        ];
    }

    public function storeComment(User $user, Video $video, string $body): Comment
    {
        abort_unless($video->processed === UploadStateEnum::COMPLETED, 403);

        $moderation = $this->moderationService->moderateText($body);

        if (!($moderation['pass'] ?? false)) {
            throw ValidationException::withMessages([
                'body' => __('site.comment_moderation_failed'),
            ]);
        }

        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->video_id = $video->id;
        $comment->body = trim($body);
        $comment->save();

        return $comment;
    }

    public function hasProcessingVideo(User $user): bool
    {
        return Video::query()
            ->where('user_id', $user->id)
            ->where('processed', UploadStateEnum::PROCESSING->value)
            ->exists();
    }

    public function uploadVideo(array $validated): Video
    {
        $description = trim((string) ($validated['description'] ?? ''));

        if ($description !== '') {
            $descriptionModeration = $this->moderationService->moderateText($description);

            if (!($descriptionModeration['pass'] ?? false)) {
                throw ValidationException::withMessages([
                    'description' => __('site.video_description_moderation_failed', ['reason' => $descriptionModeration['reason']]),
                ]);
            }
        }

        $imagePath = null;
        $videoPath = null;
        $video = null;

        try {
            $imagePath = Str::uuid() . '.' . $validated['image']->getClientOriginalExtension();
            $resizedImage = ImageManager::imagick()
                ->read($validated['image']->getRealPath())
                ->resize(320, 180);
            $imageBinary = $resizedImage->encode()->toString();
            Storage::disk('uploads_tmp')->put($imagePath, $imageBinary);

            $videoPath = Str::uuid() . '.' . $validated['video']->getClientOriginalExtension();
            Storage::disk('uploads_tmp')->putFileAs('', $validated['video'], $videoPath); // 1st param is dir under uploads_tmp

            $video = Video::create([
                'user_id' => $validated['user_id'],
                'title' => $validated['title'],
                'description' => $description !== '' ? $description : null,
                'processed' => UploadStateEnum::PROCESSING->value,
            ]); // Creation of the $video is the moment the FFMPEG Flow get dispatched

            Prometheus::counter('uploads_accepted_total')->inc(['ffmpeg']);

            SanitizeJob::dispatch($video->id, $videoPath, $imagePath, $video->title);

            return $video;
        } catch (Throwable $e) {
            if ($videoPath) {
                Storage::disk('uploads_tmp')->delete($videoPath);
            }

            if ($imagePath) {
                Storage::disk('uploads_tmp')->delete($imagePath);
            }

            if ($video) {
                $video->update([
                    'processed' => UploadStateEnum::FAILED->value,
                ]);
            }

            throw $e;
        }
    }

    public function uploadVideoOptimized(array $validated): Video
    {
        $description = trim((string) ($validated['description'] ?? ''));

        if ($description !== '') {
            $descriptionModeration = $this->moderationService->moderateText($description);

            if (!($descriptionModeration['pass'] ?? false)) {
                throw ValidationException::withMessages([
                    'description' => __('site.video_description_moderation_failed', ['reason' => $descriptionModeration['reason']]),
                ]);
            }
        }

        $titleModeration = $this->moderationService->moderateText($validated['title']);

        if (!($titleModeration['pass'] ?? false)) {
            throw ValidationException::withMessages([
                'title' => __('site.title_moderation_failed', ['reason' => $titleModeration['reason']]),
            ]);
        }

        $imagePath = null;
        $videoPath = null;
        $video = null;

        try {
            $imagePath = Str::uuid() . '.' . $validated['image']->getClientOriginalExtension();
            Storage::disk('uploads_tmp')->putFileAs('', $validated['image'], $imagePath);

            $videoPath = Str::uuid() . '.' . $validated['video']->getClientOriginalExtension();
            Storage::disk('uploads_tmp')->putFileAs('', $validated['video'], $videoPath);


            $video = Video::create([
                'user_id' => $validated['user_id'],
                'title' => $validated['title'],
                'description' => $description !== '' ? $description : null,
                'processed' => UploadStateEnum::PROCESSING->value,
            ]); // Creation of the $video is the moment the FFMPEG Flow get dispatched

            Prometheus::counter('uploads_accepted_total')->inc(['optimized']);

            OptimizedUploadJob::dispatch($video->id, $videoPath, $imagePath);

            return $video;
        } catch (Throwable $e) {
            if ($videoPath) {
                Storage::disk('uploads_tmp')->delete($videoPath);
            }

            if ($imagePath) {
                Storage::disk('uploads_tmp')->delete($imagePath);
            }

            if ($video) {
                $video->update([
                    'processed' => UploadStateEnum::FAILED->value,
                ]);

                Notification::create([
                    'user_id' => $video->user_id,
                    'notification' => 'site.notification_video_upload_failed',
                    'success' => false,
                ]);
            }

            throw $e;
        }
    }

    public function updateVideo(User $user, Video $video, array $validated): Video
    {
        if ($video->user_id !== $user->id) {
            abort(403);
        }

        $video->loadMissing('media');

        // Normalise values – empty description becomes null
        $newTitle = $validated['title'] ?? $video->title;

        $newDescription = array_key_exists('description', $validated)
            ? (trim((string) $validated['description']) ?: null)
            : $video->description;

        $imageProvided = isset($validated['image']);

        // Fill model with potential new values to detect changes
        $video->fill([
            'title'       => $newTitle,
            'description' => $newDescription,
        ]);

        $dirty = $video->getDirty();
        $titleChanged       = array_key_exists('title', $dirty);
        $descriptionChanged = array_key_exists('description', $dirty);
        $textChanged        = $titleChanged || $descriptionChanged;

        if (!$textChanged && !$imageProvided) {
            return $video;
        }

        if ($titleChanged) {
            $result = $this->moderationService->moderateText($newTitle);
            if (!($result['pass'] ?? false)) {
                throw ValidationException::withMessages([
                    'title' => __('site.title_moderation_failed', ['reason' => $result['reason']]),
                ]);
            }
        }

        if ($descriptionChanged && $newDescription !== null) {
            $result = $this->moderationService->moderateText($newDescription);
            if (!($result['pass'] ?? false)) {
                throw ValidationException::withMessages([
                    'description' => __('site.video_description_moderation_failed', ['reason' => $result['reason']]),
                ]);
            }
        }

        if ($textChanged) {
            $video->save();
        }

        if ($imageProvided) {
            $tempPath = Str::uuid() . '.' . $validated['image']->getClientOriginalExtension();
            Storage::disk('uploads_tmp')->putFileAs('', $validated['image'], $tempPath);

            $oldPublicId = $video->media()
                ->where('resource_type', 'image')
                ->value('public_id');

            UpdateVideoThumbnailJob::dispatch($video->id, $tempPath, $oldPublicId);

            return $video->refresh();
        }

        return $video->refresh();
    }

    public function deleteVideo(User $user, Video $video): void
    {
        if ($video->user_id !== $user->id) {
            abort(403);
        }

        $video->loadMissing('media');

        foreach ($video->media as $media) {
            try {
                (new UploadApi())->destroy($media->public_id, ['resource_type' => $media->resource_type]);
            } catch (Throwable $e) {
                Log::error('Failed to delete media from Cloudinary during video delete', [
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
    }

    public function toggleLike(User $user, Video $video)
    {
        abort_unless($video->processed === UploadStateEnum::COMPLETED, 403);

        $existingLike = Like::query()
            ->where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();

        if ($existingLike && $existingLike->like) {
            $existingLike->delete();

            return back()->with('success', __('site.video_unliked_successfully'));
        }

        if ($existingLike) {
            $existingLike->like = true;
            $existingLike->save();
        } else {
            $like = new Like();
            $like->user_id = $user->id;
            $like->video_id = $video->id;
            $like->like = true;
            $like->save();
        }
    }

    /**
     * Report a video by creating an alert.
     */
    public function reportVideo(User $user, Video $video, array $validated)
    {
        abort_unless($video->processed === UploadStateEnum::COMPLETED, 403);

        $alert = new Alert();
        $alert->user_id = $user->id;
        $alert->video_id = $video->id;
        $alert->reason = $validated['reason'];
        $alert->description = $validated['description'] ?? null;
        $alert->resolved = false;
        $alert->save();

        return $alert;
    }

    /**
     * Notify admins about a new video report.
     */
    public function notifyAdminsOfReport(Alert $alert)
    {
        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            $notificationMessage = "New report: {$alert->video->title} reported for {$alert->reason->value}";

            $notification = new Notification();
            $notification->user_id = $admin->id;
            $notification->notification = $notificationMessage;
            $notification->success = false;
            $notification->save();
        }
    }
}
