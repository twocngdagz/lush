<?php

namespace App\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'active',
        'deactivated_at',
        'expires_on',
        'promotion_type_id',
        'account_id',
        'kiosks',
        'subtitle',
        'description',
        'employee_access',
        'rules',
        'has_criteria',
        'has_bounce_back',
        'criteria',
        'available_all_players'
    ];
    protected $appends = ['type_identifier'];
    protected $dates = ['created_at', 'updated_at', 'expires_on', 'deleted_at'];
    protected $casts = [
        'kiosks' => 'array',
    ];

    /**
     * Boot this model
     *
     **/
    public static function boot()
    {
        parent::boot();
        static::creating(function ($promotion) {
            $promotion->active = false;
            $promotion->deactivated_at = Carbon::now();
            $promotion->index = self::count();
        });

        static::addGlobalScope('forUser', function ($query) {
            if (!auth()->user() || auth()->user()->isA('super-admin')) {
                return;
            }

            $query->forProperties(auth()->user()->property_ids);
        });
    }

    /**
     * Scope a query to only include a given type
     *
     * @return Builder
     **/
    public function scopeOfType($query, $type = null): Builder
    {
        return $type === null ? $query : $query->whereHas('type', function ($q) use ($type) {
            $q->where('identifier', $type);
        });
    }

    /**
     * Scope a query to only include drawings with Auto Submit set.
     *
     * @return Builder
     **/
    public function scopeAutoSubmitDrawings($query): Builder
    {
        return $query->whereHas('drawing', function ($query) {
            $query->where('auto_submit', '=', true);
        });
    }

    /**
     * Scope a query to only include drawings with Auto Submit Free Entry set.
     *
     * @return Builder
     **/
    public function scopeAutoSubmitFreeEntryDrawings($query, $rank_id): Builder
    {
        return $query->whereHas('drawing', function ($query) use ($rank_id) {
            $query->where('auto_submit_free_entry', '=', true)
                ->orWhereHas('freeRankedAutoSubmissions', function ($query) use ($rank_id) {
                    $query->where('rank_id', '=', $rank_id);
                });
        });
    }

    /**
     * Scope a query to only include drawings with Guest Choice set.
     *
     * @return Builder
     **/
    public function scopeGuestChoiceDrawings($query): Builder
    {
        return $query->whereHas('drawing', function ($query) {
            return $query->where('is_guest_choice', '=', true);
        });
    }

    /**
     * This method excludes non-carousel promotions
     * @return Builder
     */
    public function scopeCarouselOnly($query): Builder
    {
        return $query->whereHas('type', function ($q) {
            // $q->where('identifier', '!=', 'earnwin')->where('identifier', '!=', 'swipewin');
            $q->whereNotIn('identifier', ['earnwin', 'swipewin']);
        });
    }

    /**
     * This method excludes promotions with employee only access
     * @return Builder
     */
    public function scopeForPatrons($query): Builder
    {
        return $query->where('employee_access', 'in', [0, 2]);
    }

    /**
     * This method excludes promotions with patron only access
     * @return Builder
     */
    public function scopeForEmployees($query): Builder
    {
        return $query->where('employee_access', 'in', [1, 2]);
    }

    /**
     * This method filters to no-pin promotions only
     * @return Builder
     */
    public function scopeNoPinOnly($query): Builder
    {
        return $query->whereHas('type', function ($q) {
            $q->whereIn('identifier', ['swipewin', 'earnwin', 'drawing']);
        })->where(function ($q) {
            $q->whereHas('swipeWin', function ($q) {
                $q->where('no_pin_required', true);
            })->orWhereHas('earnWin', function ($q) {
                $q->where('no_pin_required', true);
            })->orWhereHas('drawing', function ($q) {
                $q->where('no_pin_required', true);
            });
        });
    }

    /**
     * Scope a query to only include active promotions
     *
     * Active promotions from here are those that are marked active
     * and are within their respective start/end date
     * @return Builder
     **/
    public function scopeActive($query, Carbon $theDate = null): Builder
    {
        $theDate = (null == $theDate ? now() : $theDate);

        $q = $query->where('active', true)
            ->where(function ($q) use ($theDate) {
                $q->where(function ($q) use ($theDate) {
                    $q->whereHas('drawing', function ($q) use ($theDate) {
                        $q->where('starts_at', '<=', $theDate)
                            ->where('ends_at', '>=', $theDate);
                    })
                        ->orWhereHas('earnWin', function ($q) use ($theDate) {
                            $q->where('starts_at', '<=', $theDate)
                                ->where('ends_at', '>=', $theDate);
                        })
                        ->orWhereHas('bonus', function ($q) use ($theDate) {
                            $q->where('starts_at', '<=', $theDate)
                                ->where('ends_at', '>=', $theDate);
                        })
                        ->orWhereHas('game', function ($q) use ($theDate) {
                            $q->where('starts_at', '<=', $theDate)
                                ->where('ends_at', '>=', $theDate);
                        });
                })
                    ->orWhere(function ($q) {
                        $q->doesntHave('drawing')
                            ->doesntHave('earnwin')
                            ->doesntHave('bonus')
                            ->doesntHave('game');
                    });
            });
        return $q;
    }

    public function scopeForProperties($query, $properties)
    {
        $properties = !is_array($properties) ? [$properties] : $properties;

        return $query->whereHas('properties', function ($query) use ($properties) {
            $query->whereIn('id', $properties);
        });
    }

    /**
     * Scope method to filter promotions that i can manage
     **/
    public function scopeCanManage($query): null|Builder
    {
        if (!auth()->user() or auth()->user()->isA('super-admin')) {
            return null;
        }

        return $query->forProperties(auth()->user()->property_ids);
    }

    /**
     * Is this promotion active?
     *
     * @return boolean
     **/
    public function isActive(): bool
    {
        return (bool)$this->active;
    }


    /**
     * Promotion type relationship
     *
     * @return BelongsTo
     **/
    public function type(): BelongsTo
    {
        return $this->belongsTo('PromotionType', 'promotion_type_id');
    }

    /**
     * Account relationship
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Rewards associated with this promotion
     *
     * @return HasMany
     **/
    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    /**
     * Bounce Back promotion associated with this promotion
     *
     * @return HasOne
     */
    public function bounceBackPromotion(): HasOne
    {
        return $this->hasOne(BounceBackPromotion::class);
    }

    /**
     * Rewards associated with this promotion that are available to the provided player
     * @param \App\Player $player
     * @param $player_detail
     * @return \Illuminate\Support\Collection
     */
    public function eligibleRewardsFor(Player $player, $player_detail = null): Collection
    {
        return $this->rewards()->get()->filter->playerIsEligible($player, $player_detail)->values();
    }

    /**
     * Does the provided player have access to the promotion?
     * @param \App\Player $player
     * @return boolean
     */
    public function isPlayerEligible(Player $player): bool
    {
        // If whitelisted they are eligible
        if ($this->whitelist()->where('player_id', '=', $player->id)->exists()) {
            return true;
        }

        // Check rank restrictions
        if ($this->playerRestrictedByRank($player)) {
            return false;
        }

        // Check group restrictions
        if ($this->playerRestrictedByGroup($player)) {
            return false;
        }

        // Check player restrictions
        if ($this->restrictions()->where('player_id', '=', $player->id)->exists()) {
            return false;
        }

        // Check employee restrictions
        // Patrons only = 0, Employees only = 1
        if (($player->isEmployee && $this->employee_access === 0) || (!$player->isEmployee && $this->employee_access === 1)) {
            return false;
        }

        // Check criteria restrictions
        if (!$this->playerMeetsCriteria($player)) {
            return false;
        }

        // Check bounce back criteria
        if (!$this->playerMeetsBounceBackCriteria($player)) {
            return false;
        }

        // Not sure we if we want to do this
        // Check for eligible rewards
//        if($this->eligibleRewardsFor($player)->isEmpty()){
//            return false;
//        }

        return true;
    }

    public function playerRestrictedByRank($player)
    {
        if ($this->ranks->isEmpty()) {
            return false;
        }

        return !$this->ranks->pluck('ext_rank_id')->contains($player->rank_id);
    }

    /**
     * Player Eligibility by Group
     * Players will be eligible for this promotion only if
     * they are part of the group selected on (Player Eligibility Section)
     */
    public function playerRestrictedByGroup(Player $player)
    {
        if ($this->groups->isEmpty()) {
            return false;
        }

        $groupIds = $this->groups->pluck('ext_group_id')->all();
        $inGroup = false;

        foreach ($groupIds as $groupId) {
            if ($player->originPropertyGroupIds()->contains($groupId)) {
                $inGroup = true;
                break;
            }
        }

        return !$inGroup;
    }

    /**
     * Player Restriction by Group
     * Players will not be eligible with this promotion if
     * they are part of the group selected on (Player Restriction Section)
     */
    public function playerNotExistInRestrictedGroup(Player $player)
    {
        if ($this->restrictionGroups->isEmpty()) {
            return true;
        }

        $groupIds = $this->restrictionGroups->pluck('group_id')->all();
        $inRestrictedGroup = false;

        foreach ($groupIds as $groupId) {
            if ($player->originPropertyGroupIds()->contains($groupId)) {
                $inRestrictedGroup = true;
                break;
            }
        }

        return !$inRestrictedGroup;
    }

    /**
     * Promotion earning settings
     *
     * @return PromotionEarningSetting
     **/
    public function earningSettings()
    {
        return $this->hasOne(PromotionEarningSetting::class);
    }

    /**
     * Earn methods relationship
     **/
    public function earningMethods()
    {
        return $this->hasMany(PromotionEarningMethod::class);
    }

    /**
     * Earn method types relationship
     **/
    public function earningMethodTypes()
    {
        return $this->hasManyThrough(EarningMethodType::class, PromotionEarningMethod::class, 'promotion_id', 'id', 'id', 'earning_method_type_id');
    }

    /**
     * Card image relationship for this promotion
     *
     * @return BelongsTo
     **/
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Groups relationship for promotions
     *
     * @return HasMany
     **/
    public function groups(): HasMany
    {
        return $this->hasMany(PromotionGroup::class);
    }

    /**
     * Player restrictions relationship
     * @return HasMany
     */
    public function restrictions(): HasMany
    {
        return $this->hasMany(PromotionRestriction::class)->with('player');
    }

    /**
     * Player restrictions group relationship
     * @return HasMany
     */
    public function restrictionGroups(): HasMany
    {
        return $this->hasMany(PromotionRestrictionGroup::class);
    }

    /**
     * Players that are restricted from this promotion
     * @return Collection
     */
    public function restrictedPlayers(): Collection
    {
        return $this->restrictions->map(function ($r) {
            return $r->player;
        });
    }

    /**
     * Rank relationship for promotions
     *
     * @return HasMany
     **/
    public function ranks()
    {
        return $this->hasMany(PromotionRank::class);
    }

    /**
     * Whitelist players for this promotion
     * @return BelongsToMany
     */
    public function whitelist()
    {
        return $this->hasMany(PromotionWhitelistPlayer::class)->with('player');
    }

    /**
     * Players that are whitelisted for this promotion
     *
     */
    public function whitelistedPlayers(): Collection
    {
        return $this->whitelist->map(function ($r) {
            return $r->player;
        });
    }

    /**
     * Does this promotion have group or rank access restrictions?
     * @return boolean
     */
    public function hasAccessRestrictions(): bool
    {
        return $this->groups()->exists() || $this->ranks()->exists() || $this->restrictionGroups()->exists();
    }

    /**
     * Player imports relationship
     * @return HasMany
     */
    public function playerImports(): HasMany
    {
        return $this->hasMany(PromotionPlayerImport::class);
    }

    /**
     * EarnWin Relationship
     *
     * @return App\EarnWin
     **/
    public function earnWin()
    {
        return $this->hasOne(EarnWin::class);
    }

    /**
     * Drawing Relationship
     *
     * @return App\Drawing
     **/
    public function drawing()
    {
        return $this->hasOne(Drawing::class)->with(['events']);
    }

    /**
     * Bonus Relationship
     *
     * @return App\Bonus
     **/
    public function bonus()
    {
        return $this->hasOne(Bonus::class);
    }

    /**
     * Game Relationship
     *
     * @return App\Bonus
     **/
    public function game()
    {
        return $this->hasOne(Game::class);
    }

    /**
     * Pickem Season Relationship
     *
     * @return App\Bonus
     **/
    public function pickem()
    {
        return $this->hasOne(Season::class);
    }

    /**
     * Property Relationship
     * @return BelongsToMany
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class)->withTimestamps();
    }

    /**
     * Static Promotion Relationship
     *
     * @return App\StaticPromotionAdditionalInfo
     **/
    public function additionalInfo(): StaticPromotionAdditionalInfo
    {
        return $this->hasOne(StaticPromotionAdditionalInfo::class);
    }

    /**
     * Promotion Relationship
     *
     * @return App\PromotionStaggeredRedemptionsImport
     **/
    public function staggeredRedemptionsImport()
    {
        return $this->hasMany(PromotionStaggeredRedemptionsImport::class);
    }

    /**
     * Properties that can be selected by the current user for manager redemptions
     * @return \Illuminate\Support\Collection
     */
    public function selectableProperties(): Collection
    {
        $properties = (auth()->user()->isA('super-admin')) ? Property::all() : auth()->user()->properties;

        return $this->properties->filter(function ($property) use ($properties) {
            return $properties->contains('id', $property->id);
        })->load('printers');
    }

    /**
     * @return mixed
     */
    public function getPropertiesListAttribute()
    {
        return $this->properties->implode('ext_property_id', ',');
    }

    public function getEarningMethodNamesAttribute()
    {
        return $this->earningMethodTypes->implode('name', ', ');
    }

    public function getTypeIdentifierAttribute()
    {
        return Cache::remember("promotion_type:{$this->promotion_type_id}:identifier", now()->addMinutes(90), function () {
            return $this->type->identifier;
        });
    }

    public function getTypeNameAttribute()
    {
        return Cache::remember("promotion_type_name:{$this->promotion_type_id}:name", now()->addMinutes(90), function () {
            return $this->type->name;
        });
    }

    /**
     * Get's the starts_at value from the different promotion types
     * default to created_at date
     */
    public function getStartsAtAttribute()
    {
        switch ($this->type_identifier) {
            case 'drawing':
                return $this->drawing ? $this->drawing->starts_at : $this->created_at;
            case 'earnwin':
                return $this->earnWin ? $this->earnWin->starts_at : $this->created_at;
            case 'bonus':
                return $this->bonus ? $this->bonus->starts_at : $this->created_at;
            case 'game':
                return $this->game ? $this->game->starts_at : $this->created_at;
            default:
                return $this->created_at;
        }
    }

    /**
     * Get's the ends_at value from the different promotion types
     * defaulting to expires_on date
     */
    public function getEndsAtAttribute()
    {
        switch ($this->type_identifier) {
            case 'drawing':
                return $this->drawing ? $this->drawing->ends_at : $this->expires_on;
            case 'earnwin':
                return $this->earnWin ? $this->earnWin->ends_at : $this->expires_on;
            case 'bonus':
                return $this->bonus ? $this->bonus->ends_at : $this->expires_on;
            case 'game':
                return $this->game ? $this->game->ends_at : $this->expires_on;
            default:
                return $this->expires_on;
        }
    }

    /**
     * Timestamp shortcuts for starts_at and ends_at
     */
    public function getStartsTsAttribute()
    {
        return is_object($this->starts_at) ? $this->starts_at->timestamp : null;
    }

    public function getEndsTsAttribute()
    {
        return is_object($this->ends_at) ? $this->ends_at->timestamp : null;
    }

    public function getPropertyIdsAttribute()
    {
        return $this->properties->pluck('id')->toArray();
    }

    /**
     * Quick properties check to see if we can edit this promotion.
     **/
    public function getCanEditAttribute()
    {
        if (!auth()->user() || !auth()->user()->can('edit-promotions')) {
            return false;
        } else {
            if (auth()->user()->isA('super-admin')) {
                // admin has permission to edit for any property
                return true;
            }
        }

        // User has permission to edit if from same property
        return !array_diff($this->property_ids, auth()->user()->property_ids);
    }

    /**
     * Quick properties check to see if we can approve this promotion
     **/
    public function getCanApproveAttribute()
    {
        if ($this->isMissingOriginGroup()) {
            return false;
        }

        if ($this->type_identifier == 'game') {
            // additionally depends on games 'can_approve' attribute
            return $this->game && $this->game->can_approve;
        }

        return true;
    }

    /**
     * Quick properties check to see if this promotion can be shown before pin entry
     **/
    public function getIsPinlessAttribute()
    {
        switch ($this->type_identifier) {
            case 'swipewin':
                return $this->swipeWin->no_pin_required;
            case 'earnwin':
                return $this->earnWin->no_pin_required;
            case 'drawing':
                return $this->drawing->no_pin_required;
        }
        return false;
    }

    /**
     * Check to see if this promotion has redemptions.
     * If we have redemptions then we have to prevent editing of the earning methods.
     **/
    public function getHasRedemptionsAttribute()
    {
        foreach ($this->rewards as $reward) {
            if ($reward->redemptions()->count() > 0) return true;
        }

        if ($this->type_identifier === 'bonus' && $this->bonus->bonus_multiplier_bucket_id) {
            foreach ($this->bonus->rewardPeriods()->withCount('claimedMultipliers')->get() as $rewardPeriod) {
                if ($rewardPeriod->claimed_multipliers_count > 0) return true;
            }
        }

        return false;
    }


    /**
     * Does the player meet criteria for this promotion?
     * @param \App\Player $player
     * @return bool
     * @throws \Exception
     */
    public function playerMeetsCriteria(Player $player): bool
    {
        if (!isset($this->has_criteria) || !$this->has_criteria || !Origin::supportedFeatures('promotion.all.criteria')) {
            return true;
        }

        return $player->meetsCriteria(json_decode($this->criteria, true), $this);
    }

    /**
     * Does the player meets the bounce back promotion?
     */
    public function playerMeetsBounceBackCriteria(Player $player): bool
    {
        Log::info('Checking player meets bounce back criteria');
        if (!isset($this->has_bounce_back) || !$this->has_bounce_back) {
            Log::info('Bounce Back is disabled');
            return true;
        }
        Log::info('Bounce Back is enabled');

        return $player->bounceBackEligible($this->bounceBackPromotion()->first());
    }

    /**
     * Check to see if any group associated to a promotion is made inactive from loyalty
     * @return bool
     */
    public function isMissingOriginGroup(): bool
    {
        if ($this->groups->isEmpty()) {
            return false;
        }

        $groupIds = $this->groups->pluck('ext_group_id')->all();
        $missingGroup = false;

        foreach ($groupIds as $groupId) {
            if (Origin::getPropertyGroups()->contains('id', $groupId)) {
                $missingGroup = true;
                break;
            }
        }

        return !$missingGroup;
    }

    /**
     * Draw a new reward
     *
     * Rewards redemptions cannot exceed the defined max.
     * Redraw in the event of a maxed out reward being drawn
     * @param \App\Player $player
     * @return Reward|null
     * @throws \Exception
     */
    public function drawReward(Player $player)
    {
        // get the reward pool
        $pool = $this->getRewardPool($player);
        if (is_null($pool) || $pool['total_available'] === 0) {
            throw new \Exception('No eligible rewards found for player.');
        }

        // Return a randomly selected prize from the pool using cryptographic rng
        $rng = random_int(0, $pool['total_available'] - 1);
        $reward = $pool['rewards']->first(function ($item) use ($rng) {
            return ($rng >= $item['index_start'] && $rng <= $item['index_end']);
        });

        return Reward::findOrFail($reward['id']);
    }

    /**
     * Get the array reward pool of available rewards
     * @param \App\Player $player
     * @return \Illuminate\Support\Collection|null
     */
    public function getRewardPool(Player $player)
    {
        // all remaining rewards for this promotion the player is eligible for
        $rewards = $this->rewards
            ->filter->playerIsEligible($player)
            ->where('remaining', '>', 0)
            ->values();

        if (!$rewards || $rewards->isEmpty()) {
            return null;
        }

        // create the reward pool
        $ret = ['total_available' => 0, 'rewards' => collect([])];
        $index = 0;

        // Set a default value for unlimited rewards to adapt the logic
        // behind random rewards. We can set bigger value here but for now,
        // 10 seems enough to be called `unlimited` value.
        $unlimitedValue = 10;

        $rewards->each(function ($reward) use (&$ret, &$index, $unlimitedValue) {
            $ret['total_available'] += $reward->is_reward_unlimited ? $unlimitedValue : $reward->total_available;

            $ret['rewards']->push([
                'id' => $reward->id,
                'total_available' => $reward->is_reward_unlimited ? $unlimitedValue : $reward->total_available,
                'index_start' => $index,
                'index_end' => $index + ($reward->is_reward_unlimited ? $unlimitedValue : $reward->total_available - 1),
            ]);
            $index += $reward->total_available;
        });

        return collect($ret);
    }

    /**
     * Check for a pending import
     * @param $jobName
     * @return bool
     */
    public function getHasPendingImport($jobName) {
        return $this->playerImports()->processing()->where('job_name', $jobName)->count() > 0;
    }

    /**
     * Return all kiosks the promotion is set to run on.
     *
     * @return App\Kiosk[]
     */
    public function getAvailableOnKiosksAttribute() {
        if ($this->kiosks == null) {
            return Kiosk::orderBy('name')->get();
        }

        return Kiosk::whereIn('id', $this->kiosks)->orderBy('name')->get();
    }
}
