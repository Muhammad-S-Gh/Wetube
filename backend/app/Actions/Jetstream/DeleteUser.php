<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use App\Services\VideoService;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        protected DeletesTeams $deletesTeams,
        protected VideoService $videoService
    ) {}

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $user->loadMissing(['videos.media', 'profilePicture']);

        foreach ($user->videos as $video) {
            $this->videoService->deleteVideo($user, $video);
        }

        $profilePicture = $user->profilePicture;
        if ($profilePicture?->public_id) {
            try {
                (new UploadApi())->destroy($profilePicture->public_id, [
                    'resource_type' => $profilePicture->resource_type,
                ]);

                $profilePicture->delete();
            } catch (\Throwable $e) {
                Log::error('Failed to delete user profile media from Cloudinary during account deletion', [
                    'user_id' => $user->id,
                    'public_id' => $profilePicture->public_id,
                    'resource_type' => $profilePicture->resource_type,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        }

        DB::transaction(function () use ($user) {
            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }

    /**
     * Delete the teams and team associations attached to the user.
     */
    protected function deleteTeams(User $user): void
    {
        $user->teams()->detach();

        $user->ownedTeams->each(function (Team $team) {
            $this->deletesTeams->delete($team);
        });
    }
}
