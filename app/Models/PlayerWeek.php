<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AssociatedWithAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PlayerWeek extends Model
{
    use AssociatedWithAccount;

    protected $guarded = [];
    protected $table = 'pickem_player_weeks';

    /**
     * Player Week winner selections relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function winners()
    {
        return $this->hasMany(PlayerGame::class, 'football_pickem_player_week_id');
    }

    /**
     * Week relationship
     *
     * @return BelongsTo
     **/
    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class, 'football_pickem_week_id');
    }

    /**
     * Games relationshiop
     *
     * @return HasManyThrough
     **/
    public function games(): HasManyThrough
    {
        return $this->hasManyThrough(Week::class, Game::class);
    }

    /**
     * The player this week is associated with
     *
     * @return BelongsTo
     **/
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Account relationshiop
     *
     * @return BelongsTo
     **/
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
