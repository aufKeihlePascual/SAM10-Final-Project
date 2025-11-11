@extends('layouts.app')

@section('content')

{{-- Alpine root --}}
<div x-data="{ showModal: false }">

    {{-- Sync + Add Streamer Button --}}
    <div class="px-4 mt-4 mb-4 flex justify-end space-x-2">
        <form action="{{ route('streams.sync') }}" method="GET">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Sync Streams
            </button>
        </form>

        {{-- Trigger Modal --}}
        <button @click="showModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Add New Youtuber
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded px-4">
        {{ session('success') }}
    </div>
    @endif

    {{-- Modal --}}
    <div x-show="showModal" x-transition class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
        <div @click.away="showModal = false" class="bg-white rounded shadow-lg max-w-md w-full p-6 relative">
            <h2 class="text-lg font-semibold mb-4">Add New Streamer</h2>
            <form action="{{ route('streamers.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-gray-700">YouTube Channel URL</label>
                    <input type="url" name="youtube_url" class="w-full border rounded px-3 py-2" placeholder="https://www.youtube.com/@FUWAMOCOch" required>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Add Streamer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <h1 class="text-2xl font-bold mb-2 px-4">Hololive Vtubers</h1>

    <div class="grid grid-cols-4 sm:grid-cols-5 lg:grid-cols-6 gap-2 px-4">
        @foreach ($streamers as $s)
        <div class="bg-white shadow rounded overflow-hidden flex flex-col max-w-[300px] max-h-[500px]">
            {{-- Avatar --}}
            @if($s->avatar_url)
            <div class="w-full aspect-square max-h-[250px] overflow-hidden bg-gray-100 flex items-center justify-center">
                <img src="{{ $s->avatar_url }}" class="w-full h-full object-cover" alt="{{ $s->name }}">
            </div>
            @endif

            {{-- Info --}}
            <div class="p-2 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg">
                        <a href="https://www.youtube.com/channel/{{ $s->streamer_id }}"
                            target="_blank"
                            class="text-red-400 hover:text-blue-600">
                            {{ $s->name }}
                        </a>
                    </h3>
                    <p class="text-gray-600 text-sm mt-1 break-words">
                        {{ \Illuminate\Support\Str::limit($s->description, 80) }}
                    </p>
                    <p class="text-gray-400 text-xs mt-1">
                        {{ $s->streams_count }} stream{{ $s->streams_count === 1 ? '' : 's' }}
                    </p>
                </div>

                {{-- Follow / Unfollow button --}}
                @if(in_array($s->id, $followed))
                <form method="POST" action="{{ route('streamers.unfollow', $s->id) }}" class="mt-2 mb-2">
                    @csrf
                    @method('DELETE')
                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm w-full">
                        Unfollow
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('streamers.follow', $s->id) }}" class="mt-2 mb-2">
                    @csrf
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm w-full">
                        Follow
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 px-4">
        {{ $streamers->links() }}
    </div>

    @endsection