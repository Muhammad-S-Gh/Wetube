<?php

namespace App\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ChannelService;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function __construct(
        protected ChannelService $channelService
    ) {}

    /**
     * Display channels list with lightweight stats.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $channels = $this->channelService->getChannels($search);
        return view('videos.channels', [
            'channels' => $channels,
            'search' => $search,
            'currentUserId' => $request->user()?->id,
        ]);
    }

    /**
     * Display a user's channel with all their completed videos.
     */
    public function show(User $user)
    {
        $data = $this->channelService->getChannelPageData($user);
        return view('videos.user-channel', $data);
    }
}
