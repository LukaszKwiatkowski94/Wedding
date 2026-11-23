<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineEvent extends Model
{
    protected $fillable = ['event_date', 'title', 'description'];

    protected function casts(): array
    {
        return ['event_date' => 'immutable_date'];
    }
}
