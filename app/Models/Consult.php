<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consult extends Model
{
    protected $hidden = [
        'user_id',
        'updated_at'
    ];
}
