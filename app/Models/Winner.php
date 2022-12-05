<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    protected $table = 'pickem_winners';

    /**
     * Player relationship
     *
     * @return BelongsTo
     **/
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Player week relationship
     *
     * @return BelongsTo
     **/
    public function playerWeek(): BelongsTo
    {
        return $this->belongsTo(PlayerWeek::class);
    }

    /**
     * Get the reward for this winner... if available.
     * @return [type] [description]
     */
    public function reward(): Reward
    {
        // This might be toooooo far abstracted...
        $season = $this->playerWeek->week->season;
        return $season->promotion->rewards()->skip($this->place - 1)->take(1)->first();
    }
}
