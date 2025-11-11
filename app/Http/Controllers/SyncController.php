<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Console\Commands\SyncYouTubeStreams;
use Illuminate\Support\Facades\Artisan;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $this->middleware('auth'); // Ensure only logged-in users can hit it

        try {
            // Optional: allow syncing a single streamer
            $streamerId = $request->query('streamer_id');

            $params = $streamerId ? ['--streamer_id' => $streamerId] : [];

            Artisan::call('streams:sync', $params);

            return redirect()->back()->with('success', 'Streams synced successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
