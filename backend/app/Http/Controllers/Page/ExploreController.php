<?php

namespace App\Http\Controllers\Page;

use App\Enums\UploadStateEnum;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $videos = Video::query()
            ->where('processed', UploadStateEnum::COMPLETED->value)
            ->search($search)
            ->with([
                'user:id,name,profile_photo_path',
                'media',
            ])
            ->withCount(['viewers', 'likes', 'comments'])
            ->latest('id')
            ->paginate(config('services.pagination.videos'))
            ->withQueryString();

        return view('pages.explore', [
            'videos' => $videos,
            'search' => $search,
            'currentUserId' => $request->user()?->id,
        ]);
    }
}
