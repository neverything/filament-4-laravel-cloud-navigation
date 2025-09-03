<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }
}
