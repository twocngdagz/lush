<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Week extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $dates = ['start_at', 'end_at', 'created_at', 'updated_at'];
    protected $table = 'pickem_weeks';
    protected $appends = ['voting_starts', 'voting_ends', 'voting_ends_human'];

    /**
     * Games relationship
     *
     * @return HasMany
     **/
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'football_pickem_week_id');
    }

    /**
     * What season is this related to
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'pickem_season_id');
    }

    /**
     * player weeks relationship
     *
     * @return HasMany
     **/
    public function playerWeeks(): HasMany
    {
        return $this->hasMany(PlayerWeek::class, 'football_pickem_week_id');
    }

    /**
     * Choices for the provided player
     */
    public function choicesForPlayer(Player $player): Builder
    {
        return $this->playerWeeks()->where('player_id', $player->id)->get();
    }

    /**
     * Get number of players with selections for the given account
     *
     * @return Collection
     **/
    public function playersForAccount($accountId): Collection
    {
        return $this->hasMany(PlayerWeek::class, 'football_pickem_week_id')->where('account_id', $accountId)->get();
    }

    /**
     * Results relationship for this week
     *
     * @return HasOne
     **/
    public function results(): HasOne
    {
        return $this->hasOne(Result::class, 'week_id');
    }

    /**
     * Activate this week and save
     *
     * @return void
     **/
    public function activate(): void
    {
        $this->active = true;
        $this->save();
    }

    /**
     * Deactivate this week
     *
     * @return void
     **/
    public function deactivate(): void
    {
        $this->active = false;
        $this->save();
    }

    /**
     * Get the voting_starts attribute
     *
     * @return Carbon
     **/
    public function getVotingStartsAttribute(): Carbon
    {
        if($this->games->isEmpty()) {
            return Carbon::now();
        }else{
            $firstGameStart = $this->games->first()->start_at;
            return $firstGameStart->subDays($this->season->voting_starts_at);
        }
    }

    /**
     * Get the voting_starts attribute
     *
     * @return Carbon
     **/
    public function getVotingEndsAttribute()
    {
        if($this->games->isEmpty()) {
            return Carbon::now();
        }else{
            $firstGameStart = $this->games->first()->start_at;
            return $firstGameStart->subHours($this->season->voting_ends_at);
        }
    }

    /**
     * Get the diff for humans Carbon string
     *
     * @return string
     **/
    public function getVotingEndsHumanAttribute()
    {
        return $this->voting_ends->diffForHumans();
    }

    /**
     * Boolean is this week complete?
     *
     * @return boolean
     **/
    public function isComplete()
    {
        return $this->completedGames()->count() > 0 && $this->completedGames()->count() == $this->games()->count();
    }

    /**
     * Completed games scope
     *
     * @return Illuminate\Support\Collection
     **/
    public function scopeCompletedGames()
    {
        $weekId = $this->id;
        return $this->games()->where(function($query){
            $query->whereNotNull('winner_id')->orWhere(function($query){
                $query->whereNotNull('home_team_score')->whereNotNull('away_team_score');
            });
        })->get();
    }
}
