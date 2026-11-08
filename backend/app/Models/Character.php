<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Character extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'characters';

    protected $guarded = [];

    protected $casts = [
        'external_id' => 'integer',
        'episode_count' => 'integer',
        'episodes' => 'array',
        'synced_at' => 'datetime',
    ];
}
