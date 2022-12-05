<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Origin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;


class SwipeWin extends Model
{
    protected $table = 'swipe_wins';
    protected $guarded = [];

    /**
     * Promotion relationship
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Eligible Players Relationship
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'swipe_win_players')
            ->using(SwipeWinPlayer::class)
            ->withTimestamps()
            ->withPivot('expires_at', 'kiosk_notification_id', 'reward_id', 'redeemed_at', 'declined_at')
            ->orderBy('redeemed_at')->orderBy('ext_id');
    }

    /**
     * External group relationship
     *
     * @return HasMany
     */
    public function externalGroups(): HasMany
    {
        return $this->hasMany(SwipeWinExternalGroup::class);
    }

    /**
     * Is the player currently eligible?
     */
    public function playerCurrentlyEligible($player)
    {
        return $this->players()
            ->where('player_id', $player->id)
            ->wherePivot('expires_at', '>', Carbon::now())
            ->wherePivot('declined_at', null)
            ->exists();
    }

    /**
     * Players that are current in the promotion
     */
    public function currentEligiblePlayers()
    {
        return $this->players()->wherePivot('expires_at', '>', Carbon::now())->wherePivot('declined_at', null);
    }

    /**
     * Groups that are current in the promotion
     */
    public function currentEligibleGroups()
    {
        return $this->externalGroups()->where('expires_at', '>', Carbon::now());
    }

    /**
     * Get a current group the player is in
     *
     * @param \App\Player $player
     * @return null|\App\Models\SwipeWin\SwipeWinExternalGroup
     */
    public function playerInEligibleGroup(Player $player)
    {
        if ($this->currentEligibleGroups->count() === 0) {
            return null;
        }
        $missingGroups = $this->getMissingOriginGroupExternalIds();
        // Sanity check that all groups are still active...
        if ($missingGroups->count() === $this->currentEligibleGroups->count()) {
            // If all groups are missing from the CMS set the promotion as inactive
            $this->promotion()->update(['active' => false, 'deactivated_at' => Carbon::now()]);
            return null;
        }

        // Filter out any missing groups as player is not eligible based on groups no longer active
        $activePlayerGroupIds = $player->originPropertyGroupIds()->diff($missingGroups);

        return $this->currentEligibleGroups->whereIn('ext_group_id', $activePlayerGroupIds->toArray())->first();
    }

    /**
     * Get all swipe and win redemptions for the provided reward
     * @param  Reward $reward The reward to check for redemptions
     */
    public function redemptionsForReward(Reward $reward)
    {
        return $this->players()->where(function ($q) use ($reward) {
            $q->where('reward_id', $reward->id);
            $q->where('redeemed_at', '!=', null);
        });
    }

    /**
     * All redemptions for this promotion
     */
    public function redemptions()
    {
        return $this->players()->where(function ($q) {
            $q->where('redeemed_at', '!=', null);
        });
    }

    /**
     * Check that a reward has not reached the max redemption
     */
    public function checkRewardRedemptionMax(Reward $reward)
    {
        return $this->redemptionsForReward($reward)->count() >= $reward->total_available;
    }

    /**
     *  Remove all pending kiosk notifications that were added because of eligible_all_players was enabled.
     */
    public function onDisabledAllPlayersEligibility()
    {
        $enrolledPlayers = $this->players();

        /**
         * We'll get a player object with a pivot object of App\Models\SwipeWin\SwipeWinPlayer
         * @var Player
         */
        $players = $enrolledPlayers->wherePivot('eligible_through', 'all')
            ->wherePivot('redeemed_at', null)
            ->wherePivot('declined_at', null)
            ->get();

        /**
         * Deleting the notification will also delete the SwipeWinPlayer relationship
         */
        foreach ($players as $player) {
            $player->pivot->notification->delete();
        }
    }

    /**
     * Check if player has redeemed today
     */
    public function hasRedeemedToday(Player $player)
    {
        $hasRedeemed = $this->players()
            ->where('ext_id', $player->ext_id)
            ->wherePivot('redeemed_at', '>=', date('Y-m-d').' 00:00:00')
            ->exists();

        $hasDeclined = $this->players()
            ->where('ext_id', $player->ext_id)
            ->wherePivot('declined_at', '>=', date('Y-m-d').' 00:00:00')
            ->exists();

        return $hasRedeemed || $hasDeclined;
    }

    /**
     * Enroll a player in the swipe and win
     *
     * @param  Player $player Player to enroll
     * @param  String $eligibleThrough 'all','single','group' (See Players Eligiblity Setting)
     */
    public function enrollPlayer(Player $player, $eligibleThrough = '')
    {

        // Check if has pending notification, to avoid duplications
        $kioskNotification = KioskNotification::where('notifiable_id', $player->id)
            ->where('notifiable_type', get_class($player))
            ->where('owner_id', $this->promotion->id)
            ->where('owner_type', get_class($this->promotion))
            ->where('read_at', null)
            ->exists();

        if ($kioskNotification) {
            return;
        }

        $type = KioskNotificationType::getIdentifier('swipewin');

        // Expiration date for the reward
        $expires = Carbon::now()->addDays($this->reward_expires_days);

        $notification = new KioskNotification;
        $notification->type()->associate($type);
        $notification->expires_at = $expires;
        $notification->owner_type = get_class($this->promotion);
        $notification->owner_id = $this->promotion->id;

        $player->notifications()->save($notification);

        // add the player to the promotion
        if (!$this->players()->save($player, ['expires_at' => $expires, 'kiosk_notification_id' => $notification->id, 'eligible_through' => $eligibleThrough])) {
            throw new \Exception('Your player could not be enrolled in this promotion at this time.');
        }
    }

    /**
     * Enroll a player in the swipe and win from eligible group
     *
     * @param  Player $player Player to enroll
     * @param \App\Models\SwipeWin\SwipeWinExternalGroup $group
     * @throws \Exception
     */
    public function enrollPlayerFromGroup(Player $player, SwipeWinExternalGroup $group = null)
    {
        if (!$group || now()->greaterThan($group->expires_at)) {
            return;
        }

        $type = KioskNotificationType::getIdentifier('swipewin');

        // Expiration date for the reward
        $expires = $group->expires_at;

        $notification = new KioskNotification;
        $notification->type()->associate($type);
        $notification->expires_at = $expires;
        $notification->owner_type = get_class($this->promotion);
        $notification->owner_id = $this->promotion->id;

        $player->notifications()->save($notification);

        // add the player to the promotion
        if (!$this->players()->save($player, ['expires_at' => $expires, 'kiosk_notification_id' => $notification->id])) {
            throw new \Exception('Your player could not be enrolled in this promotion at this time.');
        }
    }

    /**
     * Check to see if any group associated to a promotion is made inactive from loyalty
     * @return \Illuminate\Support\Collection
     */
    public function getMissingOriginGroupExternalIds()
    {
        if ($this->currentEligibleGroups->isEmpty()) {
            return collect();
        }

        return $this->currentEligibleGroups->pluck('ext_group_id')->diff(Origin::getPropertyGroups()->pluck('id'));
    }
}
