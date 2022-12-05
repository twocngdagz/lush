<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameFreePlayByCriteria extends Model
{
    protected $table = 'game_free_plays_by_criteria';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * Game relationship
     * @return BelongsTo
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
