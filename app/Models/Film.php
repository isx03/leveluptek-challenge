<?php

namespace App\Models;

use App\Models\Planet;
use App\Models\Character;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Film extends Model
{
    protected $fillable = [
        'id',
        'title',
        'opening_crawl',
        'director',
        'producer',
        'release_date',
        'original_json',
    ];

    protected $hidden = [
        'original_json',
        'pivot',
    ];

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'films_characters');
    }

    public function planets(): BelongsToMany
    {
        return $this->belongsToMany(Planet::class, 'films_planets');
    }
}
