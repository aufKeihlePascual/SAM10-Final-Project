<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'streamer_id',
        'title',
        'video_id',
        'video_url',
        'thumbnail_url',
        'scheduled_start',
        'status',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
    ];

    // --- Relationships ---
    public function streamer()
    {
        return $this->belongsTo(Streamer::class);
    }
}
