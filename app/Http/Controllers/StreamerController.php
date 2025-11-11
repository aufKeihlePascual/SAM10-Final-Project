<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Streamer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Log;

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
        $yt = resolve(YouTubeService::class);

        $request->validate([
            'youtube_url' => 'required|url',
        ]);

        $youtubeUrl = $request->youtube_url;

        // Parse channel ID or @handle
        if (preg_match('#youtube\.com/(?:channel/|@)([\w\-]+)#', $youtubeUrl, $matches)) {
            $identifier = $matches[1];
        } else {
            return back()->withErrors(['youtube_url' => 'Invalid YouTube URL']);
        }

        $apiKey = config('services.youtube.key');
        try {
            $channelId = null;

            // If starts with UC, treat as channel ID
            if (str_starts_with($identifier, 'UC')) {
                $channelId = $identifier;
            } else {
                // Resolve @handle via search
                $handle = ltrim($identifier, '@');
                $searchResp = Http::get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => $handle,
                    'type' => 'channel',
                    'maxResults' => 1,
                    'key' => $apiKey,
                ]);

                if (!$searchResp->ok() || empty($searchResp['items'])) {
                    return back()->withErrors(['youtube_url' => 'Failed to resolve YouTube handle to a channel']);
                }

                $channelId = $searchResp['items'][0]['snippet']['channelId'] ?? null;

                if (!$channelId) {
                    return back()->withErrors(['youtube_url' => 'Failed to resolve YouTube handle to a channel']);
                }
            }

            // Fetch full channel info
            $channelResp = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet',
                'id' => $channelId,
                'key' => $apiKey,
            ]);

            if (!$channelResp->ok() || empty($channelResp['items'])) {
                return back()->withErrors(['youtube_url' => 'Failed to fetch YouTube channel info']);
            }

            $channel = $channelResp['items'][0]['snippet'];
            $channelId = $channelResp['items'][0]['id'];

            // Create the streamer
            $streamer = Streamer::create([
                'name' => $channel['title'],
                'youtube_channel_id' => $channelId,
                'avatar_url' => $channel['thumbnails']['high']['url'] ?? null,
                'description' => $channel['description'] ?? null,
            ]);

            // Optional: Immediately sync first streams for this channel
            foreach (['live', 'upcoming'] as $status) {
                $items = $yt->fetchChannelEvents($channelId, $status);
                foreach ($items as $item) {
                    try {
                        // Store the stream in your database (you need to implement this in YouTubeService)
                        if (method_exists($yt, 'storeStream')) {
                            $yt->storeStream($streamer->id, $item);
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed saving stream {$streamer->name}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("YouTube API error: " . $e->getMessage());
            return back()->withErrors(['youtube_url' => 'Failed to fetch YouTube channel info']);
        }

        return back()->with('success', 'Streamer added successfully!');
    }

    public function debugYouTube(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
        ]);

        $youtubeUrl = $request->youtube_url;
        $apiKey = config('services.youtube.key');

        $debugData = [
            'youtube_url' => $youtubeUrl,
            'status' => 'pending',
        ];

        try {
            // Parse channel ID or handle
            if (preg_match('#youtube\.com/(?:channel/|@)([\w\-]+)#', $youtubeUrl, $matches)) {
                $identifier = $matches[1];
                $debugData['parsed_identifier'] = $identifier;
            } else {
                $debugData['error'] = 'Invalid YouTube URL';
                return response()->json($debugData);
            }

            // Resolve handle or channel ID
            if (str_starts_with($identifier, 'UC')) {
                $channelId = $identifier;
                $debugData['resolved_channel_id'] = $channelId;
            } else {
                $handle = ltrim($identifier, '@');

                $searchResp = Http::get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => $handle,
                    'type' => 'channel',
                    'maxResults' => 1,
                    'key' => $apiKey,
                ]);

                $debugData['search_response'] = $searchResp->json();

                if (!$searchResp->ok() || empty($searchResp['items'])) {
                    $debugData['error'] = 'Failed to resolve handle';
                    return response()->json($debugData);
                }

                $channelId = $searchResp['items'][0]['snippet']['channelId'] ?? null;
                $debugData['resolved_channel_id'] = $channelId;
            }

            // Fetch channel snippet info
            $channelResp = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet',
                'id' => $channelId,
                'key' => $apiKey,
            ]);

            $debugData['channel_response'] = $channelResp->json();
            $debugData['status'] = 'success';
        } catch (\Exception $e) {
            $debugData['exception'] = $e->getMessage();
            $debugData['status'] = 'error';
        }

        return response()->json($debugData);
    }
}
