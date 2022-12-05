<?php

namespace App\Services\OriginConnector\Providers\Lush\Models;

use Illuminate\Database\Eloquent\Model;

class LushPlayer extends Model
{
    protected $guarded = [];
    protected $appends = ['name', 'register_at_date', 'register_at_time'];
    protected $casts = [
        'is_excluded' => 'bool',
        'birthday' => 'date:Y-m-d',
        'id_expiration_date' => 'date:Y-m-d',
        'registered_at' => 'datetime',
        'email_opt_in' => 'bool',
        'phone_opt_in' => 'bool',
    ];
    protected $with = ['lushrank'];


    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getRegisterAtTimeAttribute()
    {
        return ($this->registered_at) ? $this->registered_at->format('H:i') : null;
    }

    public function getRegisterAtDateAttribute()
    {
        return ($this->registered_at) ? $this->registered_at->format('m/d/Y') : null;
    }

    public function setCardSwipeDataAttribute($value)
    {
        $this->attributes['card_swipe_data'] = trim(preg_replace('/\D/', '', $value));
    }


    public function lushrank()
    {
        return $this->belongsTo(LushRank::class, 'lush_rank_id');
    }

    public function lushgroups()
    {
        return $this->belongsToMany(LushGroup::class, 'lushplayers_lushgroups');
    }

    public function lushaccounts()
    {
        return $this->hasMany(LushAccount::class);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        $player = $this->find($value);

        if (!$player) {
            $swipeId = trim(preg_replace('/\D/', '', $value));
            $player = $this->where('card_swipe_data', '=', $swipeId)->firstOrFail();
        }

        if ($player->is_excluded) {
            abort(418, 'Player has been excluded.');
        }

        return $player;
    }

}
