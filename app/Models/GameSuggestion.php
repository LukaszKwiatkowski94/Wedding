<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSuggestion extends Model
{
    protected $fillable = ['name', 'description', 'guest_name'];

    protected function casts(): array
    {
        return ['published_at' => 'immutable_datetime'];
    }
}
