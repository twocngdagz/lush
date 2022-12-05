<?php

namespace App\Services\OriginConnector\Providers\Lush\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LushGroup extends Model
{
    protected $guarded = [];
    protected $appends = ['ends_at_time', 'ends_at_date', 'starts_at_date', 'starts_at_time'];
    public function lushplayers()
    {
        return $this->belongsToMany(LushPlayer::class, 'lushplayers_lushgroups');
    }

    public function getEndsAtTimeAttribute()
    {
        return ($this->ends_at) ? $this->ends_at->format('H:i') : null;
    }

    public function getEndsAtDateAttribute()
    {
        return ($this->ends_at) ? $this->ends_at->format('m/d/Y') : null;
    }

    public function getStartsAtTimeAttribute()
    {
        return ($this->starts_at) ? $this->starts_at->format('H:i') : null;
    }

    public function getStartsAtDateAttribute()
    {
        return ($this->starts_at) ? $this->starts_at->format('m/d/Y') : null;
    }

    public function getStartsAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getEndsAtAttribute($value)
    {
        return Carbon::parse($value);
    }

}
