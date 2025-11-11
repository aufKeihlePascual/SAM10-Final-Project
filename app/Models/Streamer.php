<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streamer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'youtube_channel_id',
        'avatar_url',
        'description',
    ];

    public function streams()
    {
        return $this->hasMany(Stream::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_streamer')
            ->withTimestamps();
    }
}
