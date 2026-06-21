<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WatchHistoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $history = $request->user()
            ->videoInHistory()
            ->with([
                'user:id,name,profile_photo_path',
                'media',
            ])
            ->withCount(['viewers', 'likes', 'comments'])
            ->orderByDesc('history.updated_at')
            ->paginate(config('services.pagination.videos'));

        return view('pages.history', [
            'history' => $history,
        ]);
    }
}
