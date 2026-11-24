<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    protected $fillable = ['name', 'position'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('position')->orderBy('id');
    }
}
