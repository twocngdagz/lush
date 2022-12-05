<?php

namespace App\Services\OriginConnector\Providers\Lush\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LushAccount extends Model
{
    protected $guarded = [];
    protected $appends = ['name'];
    protected $casts = [
        'is_currency' => 'bool'
    ];

    public function getNameAttribute()
    {
        return Str::title(str_replace('_', ' ', $this->type));
    }

    public function setBalanceAttribute($value)
    {
        if ($this->is_currency) {
            $this->attributes['balance'] = $value * 100;
        } else {
            $this->attributes['balance'] = $value;
        }
    }

    public function getBalanceAttribute($value)
    {
        if ($this->is_currency) {
            return $value / 100;
        }

        return $value;
    }

    public function lushplayer()
    {
        return $this->belongsTo(LushPlayer::class);
    }
}
