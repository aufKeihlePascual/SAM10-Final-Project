<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Streamer;
use Illuminate\Support\Facades\Http;

class StreamerSeeder extends Seeder
{
    public function run(): void
    {
        // List of streamers with their YouTube channel IDs and description
        $streamers = [
            [
                'name' => 'Gigi Murin',
                'youtube_channel_id' => 'UCDHABijvPBnJm7F-KlNME3w',
                'description' => 'free-spirited Chaser and mischievous gremlin! *chases u cutely* 👧',
            ],
            [
                'name' => 'Cecilia Immergreen',
                'youtube_channel_id' => 'UCvN5h1ShZtc7nly3pezRayg',
                'description' => "Ancient Automaton, with a penchant for flowers, tea, and creating music! Let's wind you up! ",
            ],
            // Add more streamers here
        ];

        foreach ($streamers as $s) {
            // Fetch channel info from YouTube Data API
            $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet',
                'id' => $s['youtube_channel_id'],
                'key' => env('YOUTUBE_API_KEY'),
            ]);

            $avatarUrl = null;

            if ($response->ok() && isset($response['items'][0]['snippet']['thumbnails']['high']['url'])) {
                $avatarUrl = $response['items'][0]['snippet']['thumbnails']['high']['url'];
            }

            // Add avatar URL to the streamer array
            $s['avatar_url'] = $avatarUrl ?? null;

            // Update or create streamer record
            Streamer::updateOrCreate(
                ['youtube_channel_id' => $s['youtube_channel_id']],
                $s
            );

            // Optional: log progress
            $this->command->info("Seeded streamer: {$s['name']} (avatar: {$avatarUrl})");
        }
    }
}
