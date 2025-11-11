<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Streamer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

    public function store(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
        ]);

        // Extract channel identifier
        $youtubeUrl = $request->youtube_url;

        // Parse channel ID or username
        // Handle URLs like /channel/ID or /@username
        if (preg_match('#youtube\.com/(?:channel/|@)([\w\-]+)#', $youtubeUrl, $matches)) {
            $channelIdentifier = $matches[1];
        } else {
            return back()->withErrors(['youtube_url' => 'Invalid YouTube URL']);
        }

        // Fetch channel info from YouTube API
        $apiKey = config('services.youtube.key'); // store your key in config/services.php
        $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'snippet',
            'forUsername' => preg_match('/^@/', $channelIdentifier) ? substr($channelIdentifier, 1) : null,
            'id' => preg_match('/^UC/', $channelIdentifier) ? $channelIdentifier : null,
            'key' => $apiKey,
        ]);

        if (!$response->ok() || empty($response['items'])) {
            return back()->withErrors(['youtube_url' => 'Failed to fetch YouTube channel info']);
        }

        $item = $response['items'][0];

        Streamer::create([
            'name' => $item['snippet']['title'],
            'youtube_channel_id' => $item['id'],
            'avatar_url' => $item['snippet']['thumbnails']['high']['url'] ?? null,
            'description' => $item['snippet']['description'] ?? null,
        ]);

        return back()->with('success', 'Streamer added successfully!');
    }
}
