<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SongSuggestion extends Model
{
    protected $fillable = ['title', 'artist', 'guest_name', 'comment'];
}
