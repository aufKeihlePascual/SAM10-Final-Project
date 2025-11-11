<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_streamer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('streamer_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'streamer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_streamer');
    }
};
