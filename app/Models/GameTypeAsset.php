<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameTypeAsset extends Model
{
    protected $fillable = ['name', 'identifier', 'description', 'filename', 'width', 'height', 'game_type_id'];

    /**
     * Game type relationship
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }
}


