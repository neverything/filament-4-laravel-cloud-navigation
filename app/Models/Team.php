<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function catalogs()
    {
        return $this->hasMany(Catalog::class);
    }

    public function links()
    {
        return $this->hasManyThrough(
            Link::class,
            Catalog::class,
            'team_id',
            'id',
            'id',
            'catalog_id'
        );
    }
}
