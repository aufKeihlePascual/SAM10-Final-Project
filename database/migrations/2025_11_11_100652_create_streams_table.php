<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('video_id')->unique();
            $table->foreignId('streamer_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('video_url');
            $table->string('thumbnail_url')->nullable();
            $table->dateTime('scheduled_start')->nullable();
            $table->enum('status', ['upcoming', 'live', 'ended'])->default('upcoming');
            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
