<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

class DrawingEvent extends Model
{
    use LogsAllActivity;

    protected $dates = ['created_at', 'updated_at', 'date', 'started_at', 'ended_at'];
    protected $guarded = [];

    /**
     * Drawing relationship
     * @return BelongsTo
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    /**
     * Rewards relationship
     * @return HasMany
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(DrawingEventReward::class);
    }

    /**
     * Event winner relationship
     * @return HasMany|Builder
     */
    public function winners(): HasMany|Builder
    {
        return $this->rewards()->whereNotNull('player_id');
    }

    /**
     * Get the claimed rewards for this event
     * @return HasMany
     */
    public function claimedRewards(): HasMany
    {
        return $this->rewards()->whereNotNull('claimed_at');
    }

    /**
     * DrawingRestriction relationship
     * @return HasMany
     */
    public function restrictions(): HasMany
    {
        return $this->hasMany(DrawingRestriction::class);
    }

    /**
     * A collection of players that are restricted from this event
     * @return Collection
     */
    public function restrictedPlayers(): Collection
    {
        return $this->restrictions->map(function ($restriction) {
            return $restriction->player;
        });
    }

    /**
     * Return the number of player restrictions that are assigned to this event
     * @return int
     */
    public function restrictionCount(): int
    {
        return $this->restrictions->map(function ($row) {
            return count(json_decode($row->data));
        })->sum();
    }

    /**
     * A drawing event is considered complete when there is at least
     * one reward and all rewards have claimed_at dates
     * @return boolean
     */
    public function isComplete(): bool
    {
        return $this->claimedRewards->count() === $this->rewards->count() && !$this->rewards->isEmpty();
    }

    /**
     * Have all the rewards been drawn
     * @return bool
     */
    public function allRewardsDrawn(): bool
    {
        return $this->rewards()->whereNotNull('drawn_at')->count() === $this->rewards->count() && !$this->rewards->isEmpty();
    }


    /**
     * Have all rewards with eligible players been drawn
     *
     * @return bool
     */
    public function allRewardsWithEligiblePlayersDrawn(): bool
    {
        return $this->rewards()->whereNull('drawn_at')->get()->filter->hasEligiblePlayers()->isEmpty();
    }

    /**
     * Get active submissions query for event
     * @return Builder
     */
    public function activeSubmissions(): Builder
    {
        if ($this->started_at || $this->drawing->is_guest_choice || $this->drawing->hasActiveEvent()) {
            // Only return submissions that have been applied to this specific event
            return $this->drawing->submissions()->where('active', '=', true)->where('event_id', '=', $this->id);
        }

        return $this->drawing->activeSubmissions();
    }

    /**
     * Get inactive submissions query for event
     * @return Builder
     */
    public function inactiveSubmissions(): Builder
    {
        if ($this->started_at || $this->drawing->is_guest_choice || $this->drawing->hasActiveEvent()) {
            // Only return submissions that have been applied to this specific event
            return $this->drawing->submissions()->where('active', '=', false)->where('event_id', '=', $this->id);
        }

        return $this->drawing->inactiveSubmissions();
    }

    public function activeSubmissionBreakdown(): Builder
    {
        $query = $this->drawing->submissionBreakdown()->where('active', '=', true);

        if ($this->started_at || $this->drawing->is_guest_choice || $this->drawing->hasActiveEvent()) {
            // Only return submissions that have been applied to this specific event
            return $query->where('event_id', '=', $this->id);
        }

        // Only return submissions that have not been applied to any event yet
        return $query->whereNull('event_id');
    }

    /**
     * Get active players for event
     * @return Collection
     */
    public function activePlayers(): Collection
    {
        return $this->activeSubmissions()->groupBy('ext_id')->get();
    }

    /**
     * Get inactive players for event
     * @return Collection
     */
    public function inactivePlayers(): Collection
    {
        return $this->inactiveSubmissions()->groupBy('ext_id')->get();
    }
}
