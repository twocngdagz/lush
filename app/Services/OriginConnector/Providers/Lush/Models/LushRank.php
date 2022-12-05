<?php

namespace App\Services\OriginConnector\Providers\Lush\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LushRank extends Model
{
    protected $guarded = [];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('threshold', 'asc');
        });
    }

    public function lushplayers()
    {
        return $this->hasMany(LushPlayer::class);
    }
}
