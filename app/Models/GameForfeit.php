<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameForfeit extends Model
{
    protected $guarded = [];

    /**
     * The player this game forfeit is associated with
     *
     * @return BelongsTo
     **/
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * The reward this game forfeit is associated with
     *
     * @return BelongsTo
     **/
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * The property this game forfeit is associated with
     *
     * @return BelongsTo
     **/
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
