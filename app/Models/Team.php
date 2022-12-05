<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $guarded = [];
    protected $appends = ['record'];
    protected $table = 'pickem_teams';
    private mixed $homeGames;

    /**
     * Get the win record in a string
     *
     * @return string
     **/
    public function getWinRecordAttribute(): string
    {
        return $this->wins()->count().'-'.$this->losses()->count();
    }

    /**
     * Home games relationship
     *
     * @return HasMany
     **/
    public function homeGames(): HasMany
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    /**
     * Away games relationship
     *
     * @return HasMany
     **/
    public function awayGames(): HasMany
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    /**
     * All games
     *
     * @return Collection
     */
    public function games(): Collection
    {
        return $this->homeGames->merge($this->awayGames);
    }

    /**
     * Get the record string for the team
     *
     * @return string
     **/
    public function getRecordAttribute(): string
    {
        return $this->wins . ' - ' . $this->losses;
    }
}
