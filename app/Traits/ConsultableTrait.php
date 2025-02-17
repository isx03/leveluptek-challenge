<?php

namespace App\Traits;

use App\Models\Consult;

trait ConsultableTrait
{
    public function saveConsult()
    {
        $consult = new Consult();
        $consult->user_id = auth('sanctum')->user()->id;
        $consult->url = request()->fullUrl();
        $consult->save();
    }
}