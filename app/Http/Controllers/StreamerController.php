<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Streamer;
use Illuminate\Support\Facades\Auth;

class StreamerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $streamers = Streamer::withCount('streams')->paginate(20);
        $user = Auth::user();
        $followed = $user->followedStreamers()->pluck('streamers.id')->toArray();

        return view('streamers.index', compact('streamers', 'followed'));
    }

    /**
     * Follow a streamer
     *
     * @param \App\Models\Streamer $streamer
     */
    public function follow(Streamer $streamer)
    {
        Auth::user()->followedStreamers()->syncWithoutDetaching([$streamer->id]);
        return back()->with('success', "You are now following {$streamer->name}");
    }

    /**
     * Unfollow a streamer
     *
     * @param \App\Models\Streamer $streamer
     */
    public function unfollow(Streamer $streamer)
    {
        Auth::user()->followedStreamers()->detach($streamer->id);
        return back()->with('success', "Unfollowed {$streamer->name}");
    }
}
