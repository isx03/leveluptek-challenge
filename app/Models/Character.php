<?php

namespace App\Models;

use App\Models\Film;
use App\Models\Planet;
use App\Models\Specie;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
    protected $fillable = [
        'id',
        'planet_id',
        'height',
        'mass',
        'hair_color',
        'skin_color',
        'eye_color',
        'birth_year',
        'gender',
        'original_json',
    ];

    protected $hidden = [
        'planet_id',
        'original_json',
        'pivot'
    ];

    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'films_characters');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'vehicles_characters');
    }

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Specie::class, 'species_characters');
    }
}
