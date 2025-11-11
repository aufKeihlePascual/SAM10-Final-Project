<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Stream;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    protected string $apiKey;
    protected string $base = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.key') ?: env('YOUTUBE_API_KEY');
    }

    public function fetchChannelEvents(string $channelId, string $eventType = 'upcoming'): array
    {
        $url = $this->base . '/search';
        $resp = Http::retry(2, 100)->get($url, [
            'part' => 'snippet',
            'channelId' => $channelId,
            'eventType' => $eventType,
            'type' => 'video',
            'maxResults' => 50,
            'key' => $this->apiKey,
        ]);

        if (! $resp->successful()) return [];

        $items = $resp->json('items', []);

        $videoIds = collect($items)->pluck('id.videoId')->filter()->unique()->values()->all();
        if (empty($videoIds)) return [];

        $videosResp = Http::retry(2, 100)->get($this->base . '/videos', [
            'part' => 'snippet,liveStreamingDetails',
            'id' => implode(',', $videoIds),
            'key' => $this->apiKey,
        ]);

        if (! $videosResp->successful()) return [];

        $videos = $videosResp->json('items', []);
        $mapped = [];

        foreach ($videos as $v) {
            $vid = $v['id'];
            $snippet = $v['snippet'] ?? [];
            $live = $v['liveStreamingDetails'] ?? [];

            $title = $snippet['title'] ?? 'Untitled';
            $thumbnail = $snippet['thumbnails']['medium']['url'] ?? ($snippet['thumbnails']['default']['url'] ?? null);
            $scheduled = $live['actualStartTime'] ?? $live['scheduledStartTime'] ?? null;
            $scheduledDate = $scheduled ? Carbon::parse($scheduled)->toDateTimeString() : null;

            $status = 'upcoming';
            if (!empty($live['concurrentViewers'])) $status = 'live';
            else if (!empty($live['actualStartTime']) && empty($live['concurrentViewers'])) $status = 'live';
            else $status = $eventType === 'live' ? 'live' : 'upcoming';

            $mapped[] = [
                'video_id' => $vid,
                'title' => $title,
                'thumbnail_url' => $thumbnail,
                'scheduled_start' => $scheduledDate,
                'status' => $status,
                'video_url' => 'https://www.youtube.com/watch?v=' . $vid,
            ];
        }

        return $mapped;
    }

    public function storeStream(int $streamerId, array $item): Stream
    {
        try {
            return Stream::updateOrCreate(
                ['video_id' => $item['video_id']],
                [
                    'streamer_id' => $streamerId,
                    'title' => $item['title'],
                    'video_url' => $item['video_url'],
                    'thumbnail_url' => $item['thumbnail_url'],
                    'scheduled_start' => $item['scheduled_start'] ?? null,
                    'status' => $item['status'],
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed saving stream for streamer {$streamerId}: " . $e->getMessage());
            throw $e;
        }
    }
}
