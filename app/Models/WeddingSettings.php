<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingSettings extends Model
{
    public const ID = 1;

    public $incrementing = false;

    protected $table = 'wedding_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ceremony_at' => 'immutable_datetime',
            'reception_at' => 'immutable_datetime',
            'songs_close_at' => 'immutable_datetime',
            'games_close_at' => 'immutable_datetime',
        ];
    }
}
