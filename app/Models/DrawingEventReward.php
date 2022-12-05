<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use App\Exceptions\Promotions\Drawing\RewardNotAssignedException;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingEventReward extends Model
{
    use LogsAllActivity;

    protected $dates = ['created_at', 'updated_at', 'drawn_at', 'claimed_at'];
    protected $guarded = [];

    /**
     * Drawing relationship
     * @return [type] [description]
     */
    public function event()
    {
        return $this->belongsTo(DrawingEvent::class, 'drawing_event_id');
    }

    /**
     * Reward relationship
     * @return BelongsTo
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Belongs to player relationship
     * @return BelongsTo
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    /**
     * Has this reward record been claimed?
     * @return boolean
     */
    public function isClaimed(): bool
    {
        return (bool)$this->claimed_at;
    }

    public function redrawAt()
    {
        return $this->drawn_at->addMinutes($this->event->countdown_minutes);
    }

    public function canRedraw(): bool
    {
        return now()->greaterThanOrEqualTo($this->redrawAt());
    }

    public function canRelease(): bool
    {
        return $this->isClaimed() || now()->greaterThanOrEqualTo($this->redrawAt());
    }

    /**
     * Claim a reward for the player in this event reward record
     * @return boolean
     */
    public function claim(): bool
    {
        if (!$this->player) {
            throw new RewardNotAssignedException;
        }

        $this->claimed_at = now();
        $this->save();

        return true;
    }

    /**
     * Check if this reward has any eligible players
     *
     * @return bool
     */
    public function hasEligiblePlayers(): bool
    {
        // Cache the response for the request as it is a little resource intensive
        return cache()->store('array')->rememberForever("DrawingEventReward:hasEligiblePlayers():{$this->id}", function () {
            try {
                return (new DrawEventReward($this->event, $this->reward))->getEligibleSubmissionPool()['total_submissions'] > 0;
            } catch (\Exception $e) {
                return false;
            }
        });
    }
}
