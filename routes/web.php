<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StreamerController;
use App\Http\Controllers\SyncController;
use App\Models\Streamer;

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/streamers', [StreamerController::class, 'index'])->name('streamers.index');
    Route::post('/streamers', [StreamerController::class, 'store'])->name('streamers.store');
    Route::post('/streamers/{streamer}/follow', [StreamerController::class, 'follow'])->name('streamers.follow');
    Route::delete('/streamers/{streamer}/unfollow', [StreamerController::class, 'unfollow'])->name('streamers.unfollow');

    Route::get('/streams/sync', [SyncController::class, 'sync'])->name('streams.sync');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
