<?php

namespace App\Models;

use App\Models\Pickem\RankedEntry;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

class Season extends Model
{
    protected $table = 'pickem_seasons';
    protected $guarded = [];
    protected $casts = [
        'eligible_days' => 'array'
    ];

    /**
     * The promotion for this table
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Weeks for this season
     *
     * @return HasMany
     **/
    public function weeks(): HasMany
    {
        return $this->hasMany(Week::class, 'pickem_season_id');
    }

    /**
     * All games for this season
     *
     * @return HasManyThrough
     **/
    public function games(): HasManyThrough
    {
        return $this->hasManyThrough(Game::class, Week::class, 'pickem_season_id', 'football_pickem_week_id');
    }

    /**
     * All player submissions for this season
     *
     * @return HasManyThrough
     **/
    public function playerWeeks(): HasManyThrough
    {
        return $this->hasManyThrough(PlayerWeek::class, Week::class, 'pickem_season_id', 'football_pickem_week_id');
    }

    /**
     * Entries that are available to players by default
     * based on their property rank (tier)
     *
     * @return HasMany
     **/
    public function rankedEntries(): HasMany
    {
        return $this->hasMany(RankedEntry::class, 'pickem_season_id');
    }

    /**
     * Get the player leaderboard for this season
     *
     * @return void
     */
    public function leaderboard(): void

    {
        $totalResults = $this->results->count();

        foreach($this->winners() as $playerId => $rankings)
        {
            //
        }
    }

    /**
     * Winners for this whole damn season
     *
     * @return Collection
     **/
    public function winners(): Collection
    {
        return $this->results->map(function($result){
            return $result->winners()->orderBy('place')->limit($this->total_winners_per_week)->get();
        })->flatten()->groupBy('player_id')->dd()->map(function($wins){
            return $wins->orderBy('place');
        });
    }

    /**
     * Pickem results relationship
     *
     * @return HasManyThrough
     **/
    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(Result::class, Week::class, 'pickem_season_id');
    }

    /**
     * Activate a week in the season
     *
     * @return void
     **/
    public function activateWeek(Week $week = null): void
    {
        if($currentWeek = $this->currentWeek()) {
            $currentWeek->deactivate();
        }

        if($week) {
            $week->activate();
        }else{
            throw new Exception('There is no eligible voting period available.', 404);
        }
    }

    /**
     * Activate the current week based on the current time
     *
     * @return void
     **/
    public function activateCurrentWeek(): void
    {
        $now = Carbon::now();

        $week = $this->weeks->where('voting_ends', '>', $now)->where('voting_starts', '<', $now)->first();

        $this->activateWeek($week);
    }

    /**
     * All player submissions for this season
     *
     * @return Collection
     **/
    public function choicesForPlayer(Player $player): Collection
    {
        return $this->playerWeeks()
            ->where('player_id', $player->id)
            ->with('week')
            ->get()->reverse();
    }

    /**
     * The current week for this season
     */
    public function currentWeek(): Week
    {
        return $this->weeks()->where('active', true)->first();
    }
}
