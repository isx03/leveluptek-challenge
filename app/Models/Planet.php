<?php

namespace App\Models;

use App\Models\Character;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planet extends Model
{
    protected $fillable = [
        'id',
        'name',
        'rotation_period',
        'orbital_period',
        'diameter',
        'climate',
        'gravity',
        'terrain',
        'surface_water',
        'population',
        'original_json',
    ];

    protected $hidden = [
        'original_json',
        'pivot',
    ];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}
