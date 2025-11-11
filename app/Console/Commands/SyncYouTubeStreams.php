<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Streamer;
use App\Models\Stream;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncYouTubeStreams extends Command
{
    protected $signature = 'streams:sync {--streamer_id= : Optional streamer id to sync only one}';
    protected $description = 'Sync upcoming, live, and ended YouTube streams for tracked streamers';

    public function handle(YouTubeService $yt)
    {
        $this->info('Starting YouTube sync...');
        $query = Streamer::query();

        if ($this->option('streamer_id')) {
            $query->where('id', $this->option('streamer_id'));
        }

        $streamers = $query->get();

        foreach ($streamers as $streamer) {
            $this->info("Syncing: {$streamer->name}");
            $currentVideoIds = [];

            foreach (['live', 'upcoming'] as $eventType) {
                $items = $yt->fetchChannelEvents($streamer->youtube_channel_id, $eventType);

                foreach ($items as $item) {
                    $currentVideoIds[] = $item['video_id'];

                    try {
                        Stream::updateOrCreate(
                            ['video_id' => $item['video_id']],
                            [
                                'streamer_id' => $streamer->id,
                                'title' => $item['title'],
                                'video_url' => $item['video_url'],
                                'thumbnail_url' => $item['thumbnail_url'],
                                'scheduled_start' => $item['scheduled_start'] ?? null,
                                'status' => $item['status'],
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed saving stream {$streamer->name}: " . $e->getMessage());
                    }
                }
            }

            // Fetch past/ended streams
            $endedStreams = $yt->fetchChannelEvents($streamer->youtube_channel_id, 'completed');
            foreach ($endedStreams as $item) {
                $currentVideoIds[] = $item['video_id'];

                Stream::updateOrCreate(
                    ['video_id' => $item['video_id']],
                    [
                        'streamer_id' => $streamer->id,
                        'title' => $item['title'],
                        'video_url' => $item['video_url'],
                        'thumbnail_url' => $item['thumbnail_url'],
                        'scheduled_start' => $item['scheduled_start'] ?? null,
                        'status' => 'ended',
                    ]
                );
            }

            // Mark any live/upcoming streams missing from current API results as ended
            Stream::where('streamer_id', $streamer->id)
                ->whereIn('status', ['live', 'upcoming'])
                ->whereNotIn('video_id', $currentVideoIds)
                ->update(['status' => 'ended']);
        }

        $this->info('YouTube sync completed.');
    }
}
