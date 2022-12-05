<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGame extends Model
{
    protected $guarded = [];
    protected $table = 'pickem_player_games';

    /**
     * Football Pickem Week relationship
     *
     * @return BelongsTo
     **/
    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    /**
     * Winner selection relationship
     *
     * @return BelongsTo
     **/
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    /**
     * Game relationship
     *
     * @return BelongsTo
     **/
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'football_pickem_game_id');
    }
}
