<?php

namespace App\Models;

use App\Traits\EarnsRewards;
use App\Traits\HasDaysOfWeek;
use App\Traits\LogsAllActivity;
use App\Exceptions\Promotions\Drawing\NoActiveSubmissionsException;



use Faker\Factory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Drawing extends Model
{
    use EarnsRewards;
    use HasDaysOfWeek;
    use LogsAllActivity;

    // submission origins
    const SUBMISSION_ORIGIN_FREE = 'free';
    const SUBMISSION_ORIGIN_FREE_RANKED = 'free_ranked';
    const SUBMISSION_ORIGIN_FREE_CRITERIA = 'free_criteria';
    const SUBMISSION_ORIGIN_FREE_DAILY = 'free_daily';
    const SUBMISSION_ORIGIN_FREE_SUBMISSION_PERIOD = 'free_submission_period';
    const SUBMISSION_ORIGIN_MULTIPLIER = 'multiplier';
    const SUBMISSION_ORIGIN_MANUAL = 'manual';
    const SUBMISSION_ORIGIN_REWARD = 'reward';
    const SUBMISSION_ORIGIN_KIOSK = 'kiosk';
    const SUBMISSION_ORIGIN_AUTO = 'auto';
    const SUBMISSION_ORIGIN_ROLLOVER = 'rollover';

    protected $guarded = [];
    protected $with = ['events', 'earningSchedule', 'submissionSchedule'];
    protected $appends = ['earning_is_active', 'submissions_are_active'];
    protected $dates = [
        'starts_at',
        'ends_at',
        'opens_at',
        'closes_at',
        'created_at',
        'updated_at',
        'earning_starts_at',
        'earning_ends_at',
        'submissions_start_at',
        'submissions_end_at',
    ];
    protected $casts = [
        'is_manual' => 'boolean',
        'is_guest_choice' => 'boolean',
        'no_pin_required' => 'boolean',
        'entry_rollover' => 'boolean',
        'entry_rollover_status' => 'boolean',
        'auto_submit' => 'boolean',
        'auto_submit_free_entries' => 'boolean',
    ];

    /**
     * Get drawings by promotion
     *
     * @param $query
     * @param $promotion
     * @return Builder
     */
    public function scopeByPromotion(Builder $query, Promotion $promotion): Builder
    {
        return $query->where('promotion_id', $promotion->id);
    }

    /**
     * Promotion relationship
     *
     * @return BelongsTo
     **/
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Reward limit relationship
     * @return HasMany
     */
    public function rewardLimits(): HasMany
    {
        return $this->hasMany(DrawingRewardLimit::class);
    }

    /**
     * Does this drawing have a reward limit of a specific type
     * @param  string $type Reward limit type in string format
     * @return DrawingRewardLimit
     */
    public function hasRewardLimit(string $type): DrawingRewardLimit
    {
        return $this->rewardLimits->where('type', $type)->first();
    }

    /**
     * Multipliers for this drawing
     * @return HasMany
     */
    public function multipliers()
    {
        return $this->hasMany(DrawingMultiplier::class);
    }

    /**
     * Earn method type relationship
     *
     **/
    public function earningMethodType()
    {
        return $this->belongsTo(EarningMethodType::class);
    }

    /**
     * Earn method type relationship
     *
     **/
    public function freeRankedEntries()
    {
        return $this->hasMany(DrawingRankedEntry::class);
    }

    /**
     * Get the number of free entries available to a specific rank
     * @param  integer $externalRankId External Rank Identifier
     * @return integer
     */
    public function freeRankedEntriesByRankId($externalRankId)
    {
        $entry = $this->freeRankedEntries()->where('ext_rank_id', $externalRankId)->first();

        return $entry->value ?? 0;
    }

    /**
     * DrawingFreeEntryByCriteria relationship
     *
     **/
    public function freeEntriesByCriteria()
    {
        return $this->hasMany(DrawingFreeEntryByCriteria::class);
    }

    /**
     * DrawingRankedAutoSubmission relationship for free entries
     *
     * @return HasMany
     */
    public function freeRankedAutoSubmissions()
    {
        return $this->hasMany(DrawingRankedAutoSubmission::class)->where('free', '=', true);
    }

    /**
     * Submissions relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submissions()
    {
        return $this->belongsToMany(Player::class, 'drawing_submissions')
            ->withPivot('origin', 'total', 'active', 'active_on', 'drawing_multiplier_id', 'event_id', 'promotion_earning_method_id', 'submitted_by')
            ->withTimestamps();
    }

    public function currentSubmissions()
    {
        if ($this->hasActiveEvent()) {
            $activeEvent = $this->events()->whereNotNull('started_at')->whereNull('ended_at')->first();
            return $this->submissions()->where('event_id', '=', $activeEvent->id);
        }

        // Get all submissions that have not been associated to an event
        // These are the submissions available now moving forward
        return $this->submissions()->whereNull('event_id');
    }

    /**
     * Total submissions for a specific type
     * or in total
     *
     * @param  string|null $origin The origin type of submission to count
     * @return integer
     */
    public function totalSubmissions($origin = null)
    {
        /**
         * If the origin is null then return the count for all submission.
         * If the origin is provided query submissions by origin
         *
         * @var QueryBuilder
         */
        $query = ($origin === null) ? $this->currentSubmissions() : $this->submissionsByOrigin($origin);

        return $query->sum('total');
    }

    /**
     * Get submissions for specified origin
     * @param $origin
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submissionsByOrigin($origin)
    {
        // Because the current state of the database allows the origin to
        // either be null or "kiosk" when it's a kiosk entry we need
        // to add a null check if the origin is "kiosk".
        if ($origin === 'kiosk') {
            return $this->currentSubmissions()->where(function ($query) use ($origin) {
                $query->where('origin', $origin)->orWhere('origin', null);
            });
        } else {
            return $this->currentSubmissions()->where('origin', $origin);
        }
    }

    /**
     * Inactive submissions for this drawing
     *
     * @return void
     **/
    public function inactiveSubmissions()
    {
        return $this->currentSubmissions()->where('active', false);
    }

    /**
     * Active submissions for this drawing
     *
     * @return void
     **/
    public function activeSubmissions()
    {
        return $this->currentSubmissions()->where('active', true);
    }

    /**
     * Get the total number of active submissions
     * @return integer
     */
    public function totalActiveSubmissions()
    {
        return max($this->activeSubmissions()->sum('total'), 0);
    }

    /**
     * Get the total number of inactive submissions
     * @return integer
     */
    public function totalInactiveSubmissions()
    {
        return max($this->inactiveSubmissions()->sum('total'), 0);
    }

    /**
     * Return query builder for drawing submission breakdown
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function submissionBreakdown()
    {
        return DB::table('drawing_submissions')
            ->selectRaw("greatest(sum(total), 0) as total")
            ->selectRaw("greatest(sum(case when active = '1' then total else 0 end), 0) as active")
            ->selectRaw("greatest(sum(case when active = '0' then total else 0 end), 0) as inactive")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_KIOSK . "' then total else 0 end), 0) as kiosk")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_AUTO . "' then total else 0 end), 0) as auto")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_MANUAL . "' then total else 0 end), 0) as manual")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_REWARD . "' then total else 0 end), 0) as reward")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_MULTIPLIER . "' then total else 0 end), 0) as multiplier")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_ROLLOVER . "' then total else 0 end), 0) as rollover")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_FREE . "' then total else 0 end), 0) as free")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_FREE_RANKED . "' then total else 0 end), 0) as free_ranked")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_FREE_SUBMISSION_PERIOD . "' then total else 0 end), 0) as free_submission_period")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_FREE_DAILY . "' then total else 0 end), 0) as free_daily")
            ->selectRaw("greatest(sum(case when origin = '" . self::SUBMISSION_ORIGIN_FREE_CRITERIA . "' then total else 0 end), 0) as free_criteria")
            ->selectRaw("greatest(sum(case when origin in ('" . self::SUBMISSION_ORIGIN_FREE . "','" . self::SUBMISSION_ORIGIN_FREE_RANKED . "','" . self::SUBMISSION_ORIGIN_FREE_SUBMISSION_PERIOD . "','" . self::SUBMISSION_ORIGIN_FREE_DAILY . "','" . self::SUBMISSION_ORIGIN_FREE_CRITERIA . "') then total else 0 end), 0) as total_free")
            ->where('drawing_id', '=', $this->id);
    }

    /**
     * Return query builder for drawing submission breakdown
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function currentSubmissionBreakdown()
    {
        $query = $this->submissionBreakdown();

        if ($this->hasActiveEvent()) {
            $activeEvent = $this->events()->whereNotNull('started_at')->whereNull('ended_at')->first();

            return $query->where('event_id', '=', $activeEvent->id);
        }

        return $query->whereNull('event_id');
    }

    /**
     * Drawing player rank association for ranked reward win limits.
     *
     * @return HasMany
     */
    public function playerRanks()
    {
        return $this->hasMany(DrawingPlayerRank::class, 'drawing_id');
    }

    /**
     * Drawing event relationahip
     * @return HasMany
     */
    public function events()
    {
        return $this->hasMany(DrawingEvent::class, 'drawing_id')->orderBy('date');
    }

    /**
     * Get a collection of events that are in the future
     * @return Collection
     */
    public function upcomingEvents()
    {
        return $this->events->where('date', '>', now());
    }

    /**
     * Does this drawing promotion have an active event
     * An event is considered active if it has a started_at date but no ended_at date
     * @return bool
     */
    public function hasActiveEvent()
    {
        return $this->events()->whereNotNull('started_at')->whereNull('ended_at')->exists();
    }

    /**
     * Get the previous event
     *
     * @param null $date date to compare (defaults to now())
     * @return DrawingEvent
     */
    public function previousEvent($date = null)
    {
        return $this->events->where('date', '<', $date ?? now())->sortByDesc('date')->first();
    }

    /**
     * Get the latest completed event
     *
     * @param null $date date to compare (defaults to now())
     * @return DrawingEvent
     */
    public function previousStartedEvent($date = null)
    {
        $query = $this->events()->where('date', '<', $date ?? now())->whereNotNull('started_at');
        // Need to clear the orderBy set in $this->events()
        $query->getBaseQuery()->orders = null;

        return $query->latest('started_at')->first();
    }

    /**
     * Is this earning period a timeframe or a repeating
     * weekly window?
     * @return boolean
     */
    public function earningIsTimeframe()
    {
        return $this->earning_starts_at != null;
    }

    /**
     * Is earning active, right now, for this drawing
     * @return boolean
     */
    public function getEarningIsActiveAttribute()
    {
        $now = now();

        $dayOfWeek = $now->dayOfWeek - 1;
        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }

        // Are we within an exclusion period
        if ($this->earningExclusionPeriods->contains->containsDates($now)) {
            return false;
        }

        // Are we within exclusion schedule
        if ($this->earningExclusionSchedule->contains->containsDates($now)) {
            return false;
        }

        if ($this->earningIsTimeframe()) {
            return $now >= $this->earning_starts_at && $now <= $this->earning_ends_at;
        } else {
            if ($schedule = $this->earningSchedule->where('day_of_week', $dayOfWeek)->first()) {
                if ($schedule->all_day) {
                    return true;
                } elseif ($now->toTimeString() >= $schedule->start_time && $now->toTimeString() <= $schedule->end_time) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Drawing earning schedule
     * @return HasMany
     */
    public function earningSchedule()
    {
        return $this->hasMany(DrawingEarningSchedule::class);
    }

    /**
     * Exclusion periods
     * @return HasMany
     */
    public function earningExclusionPeriods()
    {
        return $this->hasMany(DrawingEarningExclusionPeriod::class)->orderBy('starts_at');
    }

    /**
     * Exclusion periods
     * @return HasMany
     */
    public function earningExclusionSchedule()
    {
        return $this->hasMany(DrawingEarningExclusionSchedule::class);
    }

    /**
     * Properties where players can earn entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function earningProperties()
    {
        return $this->belongsToMany(Property::class, 'drawing_property_earn')->withTimestamps();
    }

    /**
     * Properties where players can submit entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submissionProperties()
    {
        return $this->belongsToMany(Property::class, 'drawing_property_submit')->withTimestamps();
    }

    /**
     * Properties that can be selected by the current user for manager redemptions
     * @return \Illuminate\Support\Collection
     */
    public function selectableSubmissionProperties()
    {
        $properties = (auth()->user()->isA('super-admin')) ? Property::all() : auth()->user()->properties;

        return $this->submissionProperties->filter(function ($property) use ($properties) {
            return $properties->contains('id', $property->id);
        })->load('printers');
    }

    /**
     * Is this earning period a timeframe or a repeating
     * weekly window?
     * @return boolean
     */
    public function submissionActivationsIsTimeframe()
    {
        return $this->submissions_start_at != null || $this->submissionActivationIsMinutesBeforeEvent() || $this->submissionDeactivationIsMinutesBeforeEvent();
    }

    /**
     * Does the submission period end at a date time or X minutes before event?
     * @return boolean
     */
    public function submissionDeactivationIsMinutesBeforeEvent()
    {
        return is_null($this->submissions_ends_at) && $this->submissions_end_minutes_before_event != null;
    }

    /**
     * Does the submission period start X minutes before event?
     * @return boolean
     */
    public function submissionActivationIsMinutesBeforeEvent()
    {
        return is_null($this->submissions_starts_at) && $this->submissions_start_minutes_before_event != null;
    }

    /**
     * Drawing earning schedule
     * @return HasMany
     */
    public function submissionSchedule()
    {
        return $this->hasMany(DrawingSubmissionSchedule::class);
    }

    public function getSubmissionPeriodAttribute()
    {
        return (object)[
            'uses_timeframe' => $this->submissionActivationsIsTimeframe(),
            'uses_start_minutes' => $this->submissionActivationIsMinutesBeforeEvent(),
            'uses_end_minutes' => $this->submissionDeactivationIsMinutesBeforeEvent(),
            'start_date' => ($this->submissions_start_at) ? $this->submissions_start_at->format('m/d/Y') : null,
            'start_time' => ($this->submissions_start_at) ? $this->submissions_start_at->format('H:i') : null,
            'start_minutes' => $this->submissions_start_minutes_before_event,
            'end_date' => ($this->submissions_end_at) ? $this->submissions_end_at->format('m/d/Y') : null,
            'end_time' => ($this->submissions_end_at) ? $this->submissions_end_at->format('H:i') : null,
            'end_minutes' => $this->submissions_end_minutes_before_event,
            'schedule' => $this->submissionSchedule
        ];
    }

    /**
     * Are submissions active, right now, for this drawing
     * @return boolean
     */
    public function getSubmissionsAreActiveAttribute()
    {
        $now = now();

        $dayOfWeek = $now->dayOfWeek - 1;
        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }

        if ($this->submissionActivationsIsTimeframe() && !$this->submissionDeactivationIsMinutesBeforeEvent() && !$this->submissionActivationIsMinutesBeforeEvent()) {
            return $now >= $this->submissions_start_at && $now <= $this->submissions_end_at;
        }

        if ($this->submissionActivationIsMinutesBeforeEvent()) {
            // If we have an active event - submissions are closed
            if ($this->hasActiveEvent()) {
                return false;
            }

            $next_event = $this->upcomingEvents()->first();

            // If we don't have a next event - submissions are closed
            if (!$next_event) {
                return false;
            }

            // Still before next event and the difference between now and next event is less than specified minutes
            $within_start_window = $now < $next_event->date && $now->diffInMinutes($next_event->date) < $this->submissions_start_minutes_before_event;

            if ($this->submissionDeactivationIsMinutesBeforeEvent()) {
                // If within start window and difference between now and next event is greater than specified minutes - submissions are open
                return $within_start_window && $now->diffInMinutes($next_event->date) > $this->submissions_end_minutes_before_event;
            }

            // If still before next event and the difference between now and next event is greater than specified minutes - submissions are open
            return $within_start_window;
        }

        if ($this->submissionDeactivationIsMinutesBeforeEvent()) {
            // If we have an active event - submissions are closed
            if ($this->hasActiveEvent()) {
                return false;
            }

            $next_event = $this->upcomingEvents()->first();
            // If there is not a future event - just check that we are past the promotion start at
            if (!$next_event) {
                return $now >= $this->starts_at;
            }

            // If past promotion start date and difference between now and next event is greater than specified minutes - submissions are open
            return $now >= $this->starts_at && $now->diffInMinutes($next_event->date) > $this->submissions_end_minutes_before_event;
        }

        if ($schedule = $this->submissionSchedule()->where('day_of_week', $dayOfWeek)->first()) {
            if ($schedule->all_day) {
                return true;
            }

            if ($schedule->start_minutes_before_event) {
                // If we have an active event - submissions are closed
                if ($this->hasActiveEvent()) {
                    return false;
                }

                $next_event = $this->upcomingEvents()->first();

                // If we don't have a next event - submissions are closed
                if (!$next_event) {
                    return false;
                }

                // If still before next event and the difference between now and next event is less than specified minutes - submissions are open
                return $now < $next_event->date && $now->diffInMinutes($next_event->date) < $schedule->start_minutes_before_event;
            }

            if ($schedule->end_minutes_before_event) {
                // If we have an active event - submissions are closed
                if ($this->hasActiveEvent()) {
                    return false;
                }

                $next_event = $this->upcomingEvents()->first();
                // If there is not a future event - submissions are closed
                if (!$next_event) {
                    return false;
                }
                // If difference between now and next event is greater than specified minutes - submission are open
                return $now->diffInMinutes($next_event->date) > $schedule->end_minutes_before_event;
            }

            if ($now->toTimeString() >= $schedule->start_time && $now->toTimeString() <= $schedule->end_time) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get active players for this drawing.
     * This means only players that have
     * "active" submissions in the drawing
     * @return Illuminate\Support\Collection
     */
    public function activePlayers()
    {
        return $this->activeSubmissions()->groupBy('ext_id')->get();
    }

    /**
     * Get inactive players for this drawing.
     * This means only players that have
     * "active" submissions in the drawing
     * @return Illuminate\Support\Collection
     */
    public function inactivePlayers()
    {
        return $this->inactiveSubmissions()->groupBy('ext_id')->get();
    }

    /**
     * Submissions for specific user
     *
     * @param Player $player Player eloquent object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submissionsForPlayer(Player $player)
    {
        return $this->currentSubmissions()->where('player_id', $player->id);
    }

    /**
     * Submissions for specific user by origin identifier
     *
     * @param Player $player Player eloquent object
     * @param string $origin Submission origin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submissionsForPlayerByOrigin(Player $player, $origin)
    {
        return $this->submissionsByOrigin($origin)->where('player_id', $player->id);
    }

    /**
     * Calculate the amount of multiplier earnings for the provided player
     * @param  Player $player
     * @return integer
     */
    public function totalMultiplierEntriesAvailableForPlayer(Player $player)
    {
        return $this->multiplierEntriesAvailableForPlayer($player)->sum();
    }

    /**
     * Calculate the amount of multiplier earnings for the provided player
     * @param  Player $player
     * @return Collection
     */
    public function multiplierEntriesAvailableForPlayer(Player $player)
    {
        return $this->multipliers->mapWithKeys(function ($multiplier) use ($player) {
            return [$multiplier->id => $multiplier->availableForPlayer($player)];
        })->filter();
    }

    /**
     * Get the winners for this drawing
     *
     * @see App\Models\Drawing\DrawingEvent@winners Winners are decided at the event level
     * @return Illuminate\Support\Collection
     **/
    public function winners()
    {
        return $this->events->map(function ($event) {
            return $event->winners()->get();
        })->flatten();
    }

    /**
     * Get all rewards associated through events for this drawing
     *
     * @return Illuminate\Support\Collection
     **/
    public function rewards()
    {
        return $this->hasManyThrough(DrawingEventReward::class, DrawingEvent::class);
    }


    /**
     * Get the claimed rewards for this drawing
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function claimedRewards()
    {
        return $this->rewards()->whereNotNull('claimed_at');
    }

    /**
     * Add a player to the drawing
     *
     * @param Player       $player Player eloquent object
     * @param Integer      $submissionCount number of player submissions
     * @param string|null  $origin The string identifier for the origin of the submission
     * @param boolean      $active Active vs inactive state boolean
     * @param Integer|null $multiplierId
     * @param Integer|null $event_id
     * @param Integer|null $promotion_earning_method_id
     * @param Integer|null $user_id
     * @return void
     */
    public function addPlayer(
        Player $player,
               $submissionCount = 1,
               $origin = null,
               $active = false,
               $multiplierId = null,
               $event_id = null,
               $promotion_earning_method_id = null,
               $user_id = null
    ) {
        if ($submissionCount == 0) {
            return;
        }

        $data = [
            'total'                       => $submissionCount,
            'origin'                      => $origin,
            'active'                      => $active,
            'drawing_multiplier_id'       => $multiplierId,
            'event_id'                    => $event_id,
            'promotion_earning_method_id' => $promotion_earning_method_id,
            'submitted_by'                => ($user_id) ? $user_id : auth()->user()->id ?? null,
        ];

        if ($active) {
            $data['active_on'] = now();
            // Set the player rank to save their rank
            // at the time of submission activation.
            $this->setPlayerRank($player);
        }

        return $this->submissions()->attach($player->id, $data);
    }

    /**
     * Remove manual submissions from the player record.
     * Removing manual submissions means adding a negative value
     * to the submission record.
     * @param Player $player Player eloquent object
     * @param Integer $submissionCount number of player submissions
     * @param bool $active
     * @param Int|null $event_id
     * @return boolean
     */
    public function removeManualSubmissions(Player $player, $submissionCount, $active = false, $event_id = null)
    {
        if ($submissionCount == 0) {
            return;
        }

        return $this->submissions()->attach($player->id, [
            'total'        => ($submissionCount > 0) ? (-1 * $submissionCount) : $submissionCount,
            'origin'       => 'manual',
            'active'       => $active,
            'event_id'     => $event_id,
            'submitted_by' => auth()->user()->id ?? null,
        ]);
    }

    /**
     * Activate any available "inactive" submissions for the provided player
     *
     * @param  Player $player Player to activate
     * @param null $origin
     * @return  void
     */
    public function activatePlayerSubmissions(Player $player, $origin = null)
    {
        // Set the player rank to save their rank
        // at the time of submission activation.
        $this->setPlayerRank($player);

        // Activate all inactive submissions for the provided player.
        $query = $this->submissions()->newPivotStatement()
            ->where('drawing_id', '=', $this->id)
            ->where('player_id', '=', $player->id)
            ->where('active', '=', false);

        if (!is_null($origin)) {
            if (is_array($origin)) {
                $query->whereIn('origin', $origin);
            } else {
                $query->where('origin', '=', $origin);
            }
        }

        $query->update([
            'active' => true,
            'active_on' => Carbon::now()
        ]);
    }

    /**
     * Mark all submissions as "inactive" for this player
     *
     * @param  Player $player Player to deactivate
     * @return  void
     **/
    public function deactivatePlayerSubmissions(Player $player)
    {
        $this->submissionsForPlayer($player)->updateExistingPivot($player->id, ['active' => false]);
    }

    /**
     * Set the drawing player rank for rank filtering
     * during possible winnner pool creation
     *
     * @param Player $player [description]
     */
    public function setPlayerRank(Player $player)
    {
        $this->playerRanks()->updateOrCreate(['player_id' => $player->id], ['rank_id' => $player->rank_id]);
    }

    /**
     * Boolean does this have unlimited submission?
     *
     * @return boolean
     **/
    public function hasUnlimitedSubmissions()
    {
        return !$this->max_earnable;
    }

    /**
     * Get all entries available for the provided player
     *
     * @param \App\Player $player
     * @param \Illuminate\Support\Collection $earnings_by_method
     * @return object
     * @throws \Exception
     */
    public function getAllAvailableEntries(Player $player, Collection $earnings_by_method)
    {
        $all_positive_submissions = $this->submissions()
            ->where('player_id', $player->id)
            ->where('total', '>', 0)
            ->get()
            ->pluck('pivot')
            ->groupBy('origin');

        $origin_positive_submissions = $all_positive_submissions->map(function ($origin_pivots, $origin) {
            return $origin_pivots->sum('total');
        });
        $origin_positive_submissions->put('by_method',
            $all_positive_submissions->flatten()->groupBy('promotion_earning_method_id')->map(function ($earning_pivots, $promotion_earning_method_id) {
                if ($promotion_earning_method_id) {
                    return $earning_pivots->sum('total');
                }
                return null;
            })->filter()
        );

        $available = [
            'free' => $this->getFreeEntries($origin_positive_submissions->get(self::SUBMISSION_ORIGIN_FREE, 0)),
            'free_ranked' => $this->getFreeRankedEntriesForPlayer($player, $origin_positive_submissions->get(self::SUBMISSION_ORIGIN_FREE_RANKED, 0)),
            'free_criteria' => $this->getFreeEntriesByCriteriaForPlayer($player, $origin_positive_submissions->get(self::SUBMISSION_ORIGIN_FREE_CRITERIA, 0)),
            'free_daily' => $this->getFreeDailyEntries($origin_positive_submissions->get(self::SUBMISSION_ORIGIN_FREE_DAILY, 0)),
            'free_submission_period' => $this->getFreeSubmissionPeriodEntries($origin_positive_submissions->get(self::SUBMISSION_ORIGIN_FREE_SUBMISSION_PERIOD, 0)),
            'multiplier' => $this->totalMultiplierEntriesAvailableForPlayer($player),
            'manual' => (int)$this->submissionsForPlayerByOrigin($player, self::SUBMISSION_ORIGIN_MANUAL)->where('active', false)->sum('total'),
            'rollover' => (int)$this->submissionsForPlayerByOrigin($player, self::SUBMISSION_ORIGIN_ROLLOVER)->where('active', false)->sum('total'),
            'reward' => (int)$this->submissionsForPlayerByOrigin($player, self::SUBMISSION_ORIGIN_REWARD)->where('active', false)->sum('total'),
            'auto' => (int)$this->submissionsForPlayerByOrigin($player, self::SUBMISSION_ORIGIN_AUTO)->where('active', false)->sum('total'),
            'kiosk' => (int)$this->submissionsForPlayerByOrigin($player, self::SUBMISSION_ORIGIN_KIOSK)->where('active', false)->sum('total'),
            'earned_by_method' => $this->earnedEntriesAvailable(
                $earnings_by_method,
                $origin_positive_submissions->get('by_method')
            ),
        ];
        $available['earned'] = $available['earned_by_method']->sum();

        $inactive_submissions = $this->submissions()
            ->where('player_id', $player->id)
            ->where('active', '=', false)
            ->sum('total');

        $available['total'] = max(
            $available['free']
            + $available['free_ranked']
            + $available['free_criteria']
            + $available['free_daily']
            + $available['free_submission_period']
            + $available['multiplier']
            + $available['earned']
            + $inactive_submissions, 0);

        return (object)$available;
    }

    /**
     * Get the submissions available to a player based
     * on the total submissions that have been earned,
     * the player's current submission count,
     * and the max entries available
     *
     * @param \Illuminate\Support\Collection $earnings_by_method
     * @param \Illuminate\Support\Collection|null $submissions
     * @return \Illuminate\Support\Collection
     */
    public function earnedEntriesAvailable(Collection $earnings_by_method, Collection $submissions)
    {
        $earned_by_method = collect();
        $remaining_from_max = $this->max_earnable - $submissions->sum();
        foreach ($earnings_by_method as $earning_method_id => $earnings) {
            $method_submissions = $submissions->get($earning_method_id, 0);
            if ($this->hasUnlimitedSubmissions()) {
                $earned_by_method->put($earning_method_id, max($earnings->earned_entries - $method_submissions, 0));
            } elseif ($method_submissions >= $this->max_earnable) {
                $earned_by_method->put($earning_method_id, 0);
                $remaining_from_max = 0;
            } else {
                $available = max($earnings->earned_entries - $method_submissions, 0);
                $remaining = max($remaining_from_max, 0);
                $earned_by_method->put($earning_method_id, min($available, $remaining));
                $remaining_from_max -= $remaining;
            }
        }

        return $earned_by_method;
    }

    /**
     * Get number of free entries available for provided player
     *
     * @param integer $submissions
     * @return integer
     */
    public function getFreeEntries($submissions)
    {
        // Subtract free submissions by the total of submissions added
        return max($this->free_submissions - $submissions, 0);
    }

    /**
     * Get the number of free ranked entries for a single player.
     *
     * @param  Player $player
     * @param integer $submissions
     * @return integer
     */
    public function getFreeRankedEntriesForPlayer(Player $player, $submissions)
    {
        $entries = $this->freeRankedEntriesByRankId($player->rank_id);

        // Subtract free submissions by the total of submissions added
        return max($entries - $submissions, 0);
    }

    /**
     * Get the number of free entries by eligibility criteria for provided player.
     *
     * @param  Player $player
     * @param integer $submissions
     * @return integer
     * @throws \Exception
     */
    public function getFreeEntriesByCriteriaForPlayer(Player $player, $submissions)
    {
        $freeEntries = 0;

        $entriesByCriteria = $this->freeEntriesByCriteria;
        if ($entriesByCriteria->isEmpty()) {
            return $freeEntries;
        }

        foreach ($entriesByCriteria as $row) {
            $criteria = json_decode($row['criteria'], true);
            if (!empty($criteria)) {
                if ($player->meetsCriteria($criteria, $this->promotion)) {
                    $freeEntries += $row['value'];
                }
            }
        }

        return max($freeEntries - $submissions, 0);
    }

    /**
     * Get daily free entries for provided player
     *
     * @param integer $submissions
     * @return integer
     */
    public function getFreeDailyEntries($submissions)
    {
        $entries = $this->daily_free_entries * $this->starts_at->diffInDays(now()->addDay());

        return max($entries - $submissions, 0);
    }

    /**
     * Get free submission periods entries for provided player
     *
     * @param integer $submissions
     * @return integer
     */
    public function getFreeSubmissionPeriodEntries($submissions)
    {
        $entries = ($this->submissions_are_active) ? $this->submission_period_free_entries : 0;

        return max($entries - $submissions, 0);
    }

    /**
     * getWinnerSubmissionPool
     * The winner submission pool is an array of player IDs for players
     * that have active submissions in this drawing.
     * The number of times the player ID is in the array is equal to the
     * number of eligible drawing entries they have.
     *
     * Example :
     * - Player ID 1 has four entries
     * - Player ID 2 has two entries
     *   collect([1,1,1,1,2,2])
     * @param DrawingEvent $event
     * @return Collection
     * @throws NoActiveSubmissionsException
     */
    public function getWinnerSubmissionPool(DrawingEvent $event)
    {
        /**
         * Active submissions
         * @var Collection
         */
        $submissions = $event->activeSubmissions()->get();

        /**
         * If there are no submissions then throw an error
         */
        if ($submissions->isEmpty()) {
            throw new NoActiveSubmissionsException('There are no available submissions to choose a winner.');
        }

        // Return array of total submissions keyed by player_id
        return $submissions->mapToGroups(function ($player) {
            return [$player->id => $player->pivot];
        })->map(function ($player_submissions) {
            return $player_submissions->sum('total');
        });
    }

    /**
     * Indicates if the drawing should be live because the current
     * date/time is between or equal to the drawing's start and
     * end date/times.
     *
     * @return bool
     */
    public function getIsLiveAttribute(): bool
    {
        return ($this->starts_at && $this->ends_at && Carbon::now()->between($this->starts_at, $this->ends_at));
    }

    /**
     * Indicates if the drawing's promotion has been approved and if
     * the drawing is live (the current date is between or equal
     * to the drawing's start and end date/times.
     *
     * @return bool
     */
    public function getIsActiveAndLiveAttribute(): bool
    {
        return ($this->promotion->active && $this->is_live);
    }

    /**
     * Indicates if the drawing has submissions
     * @return bool
     */
    public function getHasSubmissionsAttribute()
    {
        return $this->submissions->count() > 0;
    }

    /**
     * Get Collection of active earning periods
     * accounting for any exclusions
     *
     * @return Collection
     */
    public function getEarningPeriods()
    {
        if ($this->earningIsTimeframe()) {
            // If entries do not rollover start earnings at date of previous event
            $starts_at = (!$this->entry_rollover && $last_event = $this->previousStartedEvent()) ? $last_event->started_at : $this->earning_starts_at;
            $earning_periods = collect()->push(['starts_at' => $starts_at, 'ends_at' => $this->earning_ends_at]);
        } else {
            // If entries do not rollover start earnings at date of previous event
            $starts_at = (!$this->entry_rollover && $last_event = $this->previousStartedEvent()) ? $last_event->started_at : $this->starts_at;
            $earning_periods = collect();

            $day = $starts_at->copy();
            do {
                // Convert the day of the week.  Local uses base 0 as monday.
                // PHP uses base zero as Sunday.  This converts it but we should make
                // the change to PHP convention at some point.
                $dayOfWeek = $day->dayOfWeek - 1;
                if ($dayOfWeek < 0) {
                    $dayOfWeek = 6;
                }

                // Get the schedule for this day of the week if available
                // Continue to next day if no schedule for this day
                $schedule = $this->earningSchedule->firstWhere('day_of_week', $dayOfWeek);
                if (!$schedule) {
                    $day->addDay();
                    continue;
                }

                if ($schedule->all_day) {
                    $start = $day->copy()->startOfDay();
                    $end = $day->copy()->endOfDay();
                } else {
                    $start = $day->copy()->setTimeFromTimeString($schedule->start_time);
                    $end = $day->copy()->setTimeFromTimeString($schedule->end_time);
                }

                $earning_periods->push([
                    'starts_at' => ($starts_at->gt($start)) ? $starts_at : $start,
                    'ends_at' => ($this->ends_at->lt($end)) ? $this->ends_at : $end,
                ]);

                $day->addDay();

            } while ($day->lte($this->ends_at));
        }

        return $this->accountForEarningExclusions($earning_periods);
    }

    public function accountForEarningExclusions(Collection $earning_periods)
    {
        $periods_with_exclusions = collect();

        $earning_periods->each(function ($earning_period) use ($periods_with_exclusions) {
            $start = $earning_period['starts_at'];

            if ($this->earningExclusionPeriods->count() > 0) {
                // Account for all earning exclusion periods
                foreach ($this->earningExclusionPeriods as $exclusion_period) {
                    $end = $exclusion_period->starts_at;
                    $periods_with_exclusions->push(['starts_at' => $start, 'ends_at' => $end]);
                    $start = $exclusion_period->ends_at;
                }
            }

            if ($this->earningExclusionSchedule->count() > 0) {
                // Account for all earning exclusion schedules
                $day = $start->copy();
                do {
                    // Convert the day of the week.  Local uses base 0 as monday.
                    // PHP uses base zero as Sunday.  This converts it but we should make
                    // the change to PHP convention at some point.
                    $dayOfWeek = $day->dayOfWeek - 1;
                    if ($dayOfWeek < 0) {
                        $dayOfWeek = 6;
                    }

                    // Continue to next day if day doesn't match schedule
                    $schedule = $this->earningExclusionSchedule->firstWhere('day_of_week', $dayOfWeek);
                    if (!$schedule) {
                        $day->addDay();
                        continue;
                    }

                    if ($schedule->all_day) {
                        if ($start->lt($day->copy()->subDay()->endOfDay())) {
                            $end = $day->copy()->subDay()->endOfDay();
                            $periods_with_exclusions->push([
                                'starts_at' => $start,
                                'ends_at' => ($end->lt($earning_period['ends_at'])) ? $end : $earning_period['ends_at'],
                            ]);
                        }
                        $start = $day->copy()->addDay()->startOfDay();
                    } else {
                        $end = $day->copy()->setTimeFromTimeString($schedule->start_time)->subSecond();
                        $periods_with_exclusions->push([
                            'starts_at' => $start,
                            'ends_at' => ($end->lt($earning_period['ends_at'])) ? $end : $earning_period['ends_at'],
                        ]);
                        $start = $day->copy()->setTimeFromTimeString($schedule->end_time);
                    }
                    $day->addDay();
                } while ($start->lt($earning_period['ends_at']));

            }

            if ($start->lt($earning_period['ends_at'])) {
                $periods_with_exclusions->push(['starts_at' => $start, 'ends_at' => $earning_period['ends_at']]);
            }
        });

        return $periods_with_exclusions;
    }

    public function printEntry(Kiosk $kiosk, Player $player)
    {
        /**
         * Is this drawing manual?
         */
        if (!$this->is_manual) {
            return false;
        }

        /**
         * Does this kiosk have a printer?
         */
        if (!$kiosk->ticketPrinter || !$kiosk->ticketPrinter->adapter() || !$kiosk->ticketPrinter->adapter()->ready()) {
            return false;
        }

        $output = $this->getOutputForPrinter($player, $kiosk);

        return $kiosk->ticketPrinter->adapter()->print($output);
    }

    /**
     * Collect the data to send to the ticket printer.
     *
     * @param App\Player $player
     * @param App\Kiosk $kiosk
     * @return array
     */
    public function getOutputForPrinter(Player $player = null, $kiosk = null)
    {
        $faker = Factory::create();

        /**
         * The output array contains one element per template region.
         * The keys are ignored and are there for reference to help
         * locate where on the ticket each region is printed.
         *
         * The order of the elements must match the order of the
         * regions defined for the template. The order setup
         * below matches the template with ID Z that we use
         * to print reward tickets.
         */


        $output = [

            'legend_right' => '',

            'title' => $kiosk->account->name ?? auth()->user()->account->name,
            'subtitle_left' => 'Loyalty Kiosk',
            'subtitle_right' => 'Manual Drawing Entry',

            'headline' => $this->promotion->name,

            'barcode_bottom_left' => '',
            'barcode_bottom_right' => '',

            'barcode_footer' => now()->format(config('date_formats.date')) . '   ' . ($player ? $player->fullName . ' - ' . $player->ext_id : ''),

            'sub_barcode_footer_1' => '',
            'sub_barcode_footer_2' => '',

            'footer_headline' => '',
            'footer_subtitle_left' => 'Drawing Entry ' . 'Promo' . $this->promotion->id,
            'footer_subtitle_right' => '',

            'barcode' => $faker->numerify('##################'), // '000000000053668153',
        ];

        return $output;
    }

    public function printAndActivateEntries(
        TicketPrinter $ticketPrinter,
        Player $player,
                      $total,
                      $origin = null,
                      $active = false,
                      $multiplierId = null,
                      $event_id = null,
                      $promotion_earning_method_id = null
    ) {
        if ($total == 0) {
            return 0;
        }

        $printed = 0;
        for ($i = 0; $i < $total; $i++) {
            try {
                $output = $this->getOutputForPrinter($player, $ticketPrinter->kiosk);

                $ticketPrinter->adapter()->print($output, 'Z');
                $printed++;
            } catch (\Exception $e) {

                if ($origin === self::SUBMISSION_ORIGIN_MANUAL) {
                    // Delete all current inactive manual submissions
                    $this->submissions()->newPivotStatement()
                        ->where('player_id', '=', $player->id)
                        ->where('active', '=', false)
                        ->delete();

                    // Add activated manual submissions
                    $this->addPlayer($player, $printed, $origin, $active, $multiplierId, $event_id, $promotion_earning_method_id);
                    // Add inactive remaining manual entries
                    $remaining = $total - $printed;
                    $this->addPlayer($player, $remaining, $origin, false, $multiplierId, $event_id, $promotion_earning_method_id);
                } else {
                    // Add submission for successfully printed entries
                    $this->addPlayer($player, $printed, $origin, $active, $multiplierId, $event_id, $promotion_earning_method_id);
                }
                throw $e;
            }
        }
        if ($origin === self::SUBMISSION_ORIGIN_MANUAL) {
            // Activate manual submissions as all printed successfully
            $this->activatePlayerSubmissions($player);
        } else {
            // Add submission for successfully printed entries
            $this->addPlayer($player, $printed, $origin, $active, $multiplierId, $event_id, $promotion_earning_method_id);
        }

        return $printed;
    }


}
