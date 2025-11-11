<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Stream;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $followedIds = $user->followedStreamers()->pluck('streamers.id')->all();

        $streams = Stream::with('streamer')
            ->whereIn('streamer_id', $followedIds)
            ->whereIn('status', ['live', 'upcoming'])
            ->orderByRaw("FIELD(status,'live','upcoming')")
            ->orderBy('scheduled_start', 'asc')
            ->get();

        $live = $streams->where('status', 'live');
        $upcoming = $streams->where('status', 'upcoming');

        $ended = Stream::with('streamer')
            ->whereIn('streamer_id', $followedIds)
            ->where('status', 'ended')
            ->orderBy('scheduled_start', 'desc')
            ->paginate(8);

        return view('dashboard.index', compact('live', 'upcoming', 'ended'));
    }
}
