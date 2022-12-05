<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use \Origin;

class DrawingMultiplier extends Model
{
    use LogsAllActivity;

    protected $guarded = [];
    protected $dates = ['start_at', 'end_at', 'created_at', 'updated_at'];

    /**
     * Drawing relationship
     * @return BelongsTo
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    /**
     * Property Relationship
     * @return BelongsToMany
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class)->withTimestamps();
    }

    public function restrictedPlayers(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'drawing_multiplier_restrictions')->withTimestamps();
    }

    public function invitedPlayers(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'drawing_multiplier_invited_players')->withTimestamps();
    }

    /**
     * Submissions associated with this multiplier
     * @return HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(DrawingSubmission::class)->where('origin', '=', Drawing::SUBMISSION_ORIGIN_MULTIPLIER);
    }

    /**
     * Submissions for a specific player
     * @param  Player $player
     * @return integer
     */
    public function submissionsForPlayer(Player $player): int
    {
        return $this->submissions()->where('player_id', $player->id)->sum('total');
    }

    /**
     * Multiplier entries available to the player
     * @param  Player $player
     * @return integer
     */
    public function availableForPlayer(Player $player): int
    {
        if ($this->playerIsRestricted($player)) {
            return 0;
        }


        $earnings_by_method = collect();
        /** @var Drawing $drawing */
        $drawing = $this->drawing()->setEagerLoads([])->first();
        $earning_methods = $drawing->promotion->earningMethods()->with('type')->get();
        $properties = ($this->properties->isNotEmpty()) ? $this->properties : $this->drawing->earningProperties;
        foreach ($earning_methods as $earning_method) {
            $earnings_for_player = $drawing->earningsForPlayer($player, $earning_method->type, $this->start_at, $this->end_at,
                $properties->implode('ext_property_id', ','));

            $earnings_by_method->put($earning_method->id, (object)[
                'earnings' => $earnings_for_player,
                'earned_entries' => $drawing->rewardsEarned($earnings_for_player, $earning_method),
            ]);
        }

        $submissionsEarned = $earnings_by_method->sum('earned_entries');

        /**
         * Existing entries for this multiplier and this player
         */
        $existingSubmissions = $this->submissions()
            ->where('player_id', $player->id)
            ->where('total', '>', 0)
            ->sum('total');

        return max(($submissionsEarned * $this->multiply_by) - $submissionsEarned - $existingSubmissions, 0);
    }

    /**
     * Is this player restricted from using the multiplier?
     *
     * @param $player
     * @return bool
     */
    public function playerIsRestricted(Player $player): bool
    {
        // If we have invited players we only allow those invited to use the multiplier
        // Return if the current player was invited
        if ($this->invitedPlayers()->count() > 0) {
            return !$this->invitedPlayers()->where('player_id', '=', $player->id)->exists();
        }

        // If player is restricted always return true
        if ($this->restrictedPlayers()->where('player_id', '=', $player->id)->exists()) {
            return true;
        }

        // Check if rank restriction is set on multiplier and equals rank of player
        if (!is_null($this->rank_id) && $this->rank_id !== (int)$player->rank_id) {
            return true;
        }

        return false;
    }

    /**
     * Total number of submissions for this multiplier
     * @return integer
     */
    public function totalSubmissions(): int
    {
        return $this->submissions()->sum('total');
    }

    /**
     * Calculate an earning multiplier for this
     * @param  integer $value The value to multiply.
     * @return integer
     */
    public function calculate($value): int
    {
        if ($value <= 0) {
            return 0;
        }

        return $value * $this->multiply_by;
    }

    /**
     * Getter for rank name
     * @param string $default
     * @return string
     */
    public function getRankName(string $default = '-'): string
    {
        return Origin::getPropertyRanks()->keyBy('id')->get($this->rank_id)->name ?? $default;
    }
}
