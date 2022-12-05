<?php

namespace App\Traits;

use App\Models\Game;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait GameMethods {
    /**
     * Game Relationship
     *
     * @return HasOne
     */
    public function game(): HasOne
    {
        return $this->hasOne(Game::class);
    }
}
