@extends('layouts.app')

@section('content')

{{-- Sync Button --}}
<div class="px-4 mb-4">
    <form action="{{ route('streams.sync') }}" method="GET">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Sync Streams</button>
    </form>
</div>

@if(session('success'))
<div class="px-4 mb-4">
    <div class="p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
</div>
@endif

<div class="px-4">
    <h1 class="text-2xl font-bold mb-4">Your Stream Feed</h1>

    {{-- No streams --}}
    @if($live->isEmpty() && $upcoming->isEmpty())
    <div class="p-4 bg-blue-100 text-blue-800 rounded mb-4">
        No upcoming/live streams. Try following some <a href="{{ route('streamers.index') }}" class="underline font-semibold">streamers</a>.
    </div>
    @endif

    {{-- Live Streams --}}
    @if($live->isNotEmpty())
    <h2 class="text-xl font-semibold mb-2">Now Live</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        @foreach($live as $s)
        <div class="bg-white shadow rounded overflow-hidden flex flex-col">
            <img src="{{ $s->thumbnail_url }}" class="h- h-48 object-cover" alt="{{ $s->title }}">
            <div class="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg truncate" title="{{ $s->title }}">{{ $s->title }}</h3>
                    <p class="text-gray-600">{{ $s->streamer->name }}</p>
                </div>
                <a href="{{ $s->video_url }}" target="_blank" class="mt-3 inline-block bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-center">
                    Watch on YouTube
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Upcoming Streams --}}
    @if($upcoming->isNotEmpty())
    <h2 class="text-xl font-semibold mb-2">Upcoming</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6 px-4">
        @foreach($upcoming as $s)
        <div class="bg-white shadow rounded overflow-hidden flex flex-col">
            {{-- Thumbnail container, fixed square --}}
            <div class="w-full aspect-square overflow-hidden bg-gray-100 flex items-center justify-center">
                {{-- Use high-res thumbnail --}}
                <img src="{{ str_replace('mqdefault', 'hqdefault', $s->thumbnail_url) }}"
                    class="w-full h-full object-contain"
                    alt="{{ $s->title }}">
            </div>

            {{-- Info & button --}}
            <div class="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg truncate" title="{{ $s->title }}">{{ $s->title }}</h3>
                    <p class="text-gray-600">{{ $s->streamer->name }}</p>
                    <p class="text-gray-400 text-sm">
                        Scheduled: {{ $s->scheduled_start ? $s->scheduled_start->toDayDateTimeString() : 'TBA' }}
                    </p>
                </div>
                <a href="{{ $s->video_url }}" target="_blank"
                    class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-center">
                    Watch on YouTube
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Finished Streams --}}
    @if($ended->isNotEmpty())
    <h2 class="text-xl font-semibold mb-2">Finished Streams</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6">
        @foreach($ended as $s)
        <div class="bg-white shadow rounded overflow-hidden flex flex-col">
            <img src="{{ $s->thumbnail_url }}" class="w-full h-48 object-cover" alt="{{ $s->title }}">
            <div class="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg truncate" title="{{ $s->title }}">{{ $s->title }}</h3>
                    <p class="text-gray-600">{{ $s->streamer->name }}</p>
                    <p class="text-gray-400 text-sm">Scheduled: {{ $s->scheduled_start ? $s->scheduled_start->toDayDateTimeString() : 'TBA' }}</p>
                </div>
                <a href="{{ $s->video_url }}" target="_blank" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-center">
                    Watch on YouTube
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($ended->lastPage() > 1)
    <div class="flex justify-center mt-4 mb-6 space-x-1">
        <a href="{{ $ended->url(1) }}" class="rounded-full border border-gray-300 py-2 px-3 text-center text-sm transition-all shadow-sm hover:shadow-md text-gray-600 hover:text-white hover:bg-gray-700 hover:border-gray-700 disabled:pointer-events-none disabled:opacity-50 {{ $ended->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">First</a>
        <a href="{{ $ended->previousPageUrl() }}" class="rounded-full border border-gray-300 py-2 px-3 text-center text-sm transition-all shadow-sm hover:shadow-md text-gray-600 hover:text-white hover:bg-gray-700 hover:border-gray-700 disabled:pointer-events-none disabled:opacity-50 {{ $ended->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">Prev</a>
        @foreach(range(1, $ended->lastPage()) as $page)
        <a href="{{ $ended->url($page) }}" class="min-w-9 rounded-full py-2 px-3.5 text-center text-sm transition-all shadow-sm border {{ $ended->currentPage() == $page ? 'bg-gray-700 text-white border-transparent shadow-md' : 'border-gray-300 text-gray-600 hover:text-white hover:bg-gray-700 hover:border-gray-700 hover:shadow-md' }}">{{ $page }}</a>
        @endforeach
        <a href="{{ $ended->nextPageUrl() }}" class="rounded-full border border-gray-300 py-2 px-3 text-center text-sm transition-all shadow-sm hover:shadow-md text-gray-600 hover:text-white hover:bg-gray-700 hover:border-gray-700 disabled:pointer-events-none disabled:opacity-50 {{ $ended->currentPage() == $ended->lastPage() ? 'opacity-50 pointer-events-none' : '' }}">Next</a>
        <a href="{{ $ended->url($ended->lastPage()) }}" class="rounded-full border border-gray-300 py-2 px-3 text-center text-sm transition-all shadow-sm hover:shadow-md text-gray-600 hover:text-white hover:bg-gray-700 hover:border-gray-700 disabled:pointer-events-none disabled:opacity-50 {{ $ended->currentPage() == $ended->lastPage() ? 'opacity-50 pointer-events-none' : '' }}">Last</a>
    </div>
    @endif
    @endif
</div>

@endsection