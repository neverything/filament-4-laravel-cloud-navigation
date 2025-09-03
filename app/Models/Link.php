<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Link extends Model
{
    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }

    public function team(): HasOneThrough|Link
    {
        return $this->hasOneThrough(
            Team::class,
            Catalog::class,
            'id',
            'id',
            'catalog_id',
            'team_id'
        );
    }
}
