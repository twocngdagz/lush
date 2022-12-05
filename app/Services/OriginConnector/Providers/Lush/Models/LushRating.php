<?php

namespace App\Services\OriginConnector\Providers\Lush\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LushRating extends Model
{
    protected $guarded = [];
    protected $appends = ['seconds_played', 'gaming_date'];
    protected $dates = ['starts_at', 'ends_at'];
    protected $with = ['lushplayer'];

    public function lushplayer()
    {
        return $this->belongsTo(LushPlayer::class, 'lush_player_id');
    }

    public function setCashInAttribute($value)
    {
        $this->attributes['cash_in'] = $value * 100;
    }

    public function getCashInAttribute($value)
    {
        return $value / 100;
    }

    public function setTheoWinAttribute($value)
    {
        $this->attributes['theo_win'] = $value * 100;
    }

    public function getTheoWinAttribute($value)
    {
        return $value / 100;
    }

    public function setActualWinAttribute($value)
    {
        $this->attributes['actual_win'] = $value * 100;
    }

    public function getActualWinAttribute($value)
    {
        return $value / 100;
    }

    public function setCompEarnedAttribute($value)
    {
        $this->attributes['comp_earned'] = $value * 100;
    }

    public function getCompEarnedAttribute($value)
    {
        return $value / 100;
    }

    public function getSecondsPlayedAttribute()
    {
        return ($this->starts_at) ? $this->starts_at->diffInSeconds($this->ends_at) : 0;
    }

    public function getGamingDateAttribute()
    {
        return ($this->starts_at) ? $this->starts_at->format('Y-m-d') : null;
    }

    public function scopePlayAfter(Builder $query, $start_date)
    {
        return $query->where('starts_at', '>=', $start_date);
    }

    public function scopePlayBefore(Builder $query, $start_date)
    {
        return $query->where('starts_at', '<', $start_date);
    }

    public function scopePlayBetween(Builder $query, $start_date, $end_date)
    {
        return $query->whereBetween('starts_at', [$start_date, $end_date]);
    }

    public function getStartsAtDateAttribute()
    {
        return ($this->starts_at) ? $this->starts_at->format('m/d/Y') : null;
    }

    public function getStartsAtTimeAttribute()
    {
        return ($this->starts_at) ? $this->starts_at->format('H:i') : null;
    }

    public function getEndsAtDateAttribute()
    {
        return ($this->ends_at) ? $this->ends_at->format('m/d/Y') : null;
    }

    public function getEndsAtTimeAttribute()
    {
        return ($this->ends_at) ? $this->ends_at->format('H:i') : null;
    }
}
