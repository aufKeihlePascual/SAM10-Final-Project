@extends('layouts.app')

@section('content')

{{-- Sync Button --}}
<form action="{{ route('streams.sync') }}" method="GET" class="mb-4">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Sync Streams</button>
</form>

@if(session('success'))
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    {{ session('success') }}
</div>
@endif

<h1 class="text-2xl font-bold mb-4">Tracked Streamers</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($streamers as $s)
    <div class="bg-white shadow rounded overflow-hidden">
        @if($s->avatar_url)
        <img src="{{ $s->avatar_url }}" class="w-full h-48 object-cover" alt="{{ $s->name }}">
        @endif
        <div class="p-4">
            <h3 class="font-bold text-lg">{{ $s->name }}</h3>
            <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($s->description, 120) }}</p>
            <p class="text-gray-400 text-sm">{{ $s->streams_count }} streams recorded</p>

            @if(in_array($s->id, $followed))
            <form method="POST" action="{{ route('streamers.unfollow', $s->id) }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm w-full">Unfollow</button>
            </form>
            @else
            <form method="POST" action="{{ route('streamers.follow', $s->id) }}" class="mt-2">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm w-full">Follow</button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $streamers->links() }}
</div>

@endsection