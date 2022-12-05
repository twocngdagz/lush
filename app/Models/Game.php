<?php

namespace App\Models;

use App\Traits\EarnsRewards;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Ramsey\Collection\Collection;

class Game extends Model
{
    use EarnsRewards;

    protected $guarded = [];
    protected $fillable = [
        'game_title',
        'promotion_id',
        'game_type_id',
        'starts_at',
        'ends_at',
        'max_earnable',
        'daily_free_games',
        'win_levels',
        'win_animation'
    ];
    protected $dates = ['created_at', 'updated_at', 'starts_at', 'ends_at'];
    protected $appends = ['type_identifier'];

    public function setDailyFreeGamesAttribute($value): void
    {
        $this->attributes['daily_free_games'] = $value ?: 0;
    }

    /**
     * Promotion relationship
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Game type relationship
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    /**
     * Assets related to this promotion
     *
     **/
    public function assets(): HasMany
    {
        return $this->hasMany(GameAsset::class);
    }

    /**
     * Rewards related to this promotion
     *
     **/
    public function rewards(): Collection
    {
        return $this->promotion->rewards();
    }

    /**
     * Plays of the game
     */
    public function plays(): Builder
    {
        return $this->hasMany(GamePlay::class)->orderBy('created_at');
    }

    /**
     * Forfeits of the game
     */
    public function forfeits(): HasMany
    {
        return $this->hasMany(GameForfeit::class)->orderBy('created_at');
    }

    /**
     * Properties where players can earn rewards
     *
     * @return BelongsToMany
     */
    public function earningProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'game_property_earn')->withTimestamps();
    }

    /**
     * Properties where players can redeem rewards
     *
     * @return BelongsToMany
     */
    public function redemptionProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'game_property_redeem')->withTimestamps();
    }

    public function gameFreePlays(): HasMany
    {
        return $this->hasMany(GameFreePlay::class);
    }

    /**
     * Earn method type relationship
     *
     **/
    public function freeRankedPlays(): HasMany
    {
        return $this->hasMany(GameRankedPlay::class);
    }

    /**
     * Get the number of free entries available to a specific rank
     * @param integer $externalRankId External Rank Identifier
     * @return integer
     */
    public function freeRankedPlaysByRankId(int $externalRankId): int
    {
        $entry = $this->freeRankedPlays()->where('ext_rank_id', $externalRankId)->first();

        return $entry->value ?? 0;
    }

    /**
     * GameFreeEntryByCriteria relationship
     *
     **/
    public function freePlaysByCriteria(): HasMany
    {
        return $this->hasMany(GameFreePlayByCriteria::class);
    }

    /**
     * Redemptions for a specific reward
     * @param Reward $reward
     * @return Collection
     */
    public function redemptionsForReward(Reward $reward): Collection
    {
        return $this->plays()->where('reward_id', $reward->id)->get();
    }

    /**
     * Forfeits for a specific player
     * @param Player $player
     * @return Collection
     */
    public function forfeitsForPlayer(Player $player): Collection
    {
        return $this->forfeits()->where('player_id', $player->id)->get();
    }

    /**
     * Get cache asset images
     */
    public function getAssetImages():object
    {
        return Cache::remember($this->getAssetCacheKey(), now()->addMinutes(5), function() {
            return $this->assets()->with('image')->get()->map(function($asset) {
                return (object) array_merge(['identifier' => $asset->type_identifier], $asset->image->toArray());
            });
        });
    }

    public function getMissingAssets(): array
    {
        return array_diff($this->type_required_assets, $this->getAssetImages()->pluck('identifier')->toArray());
    }

    public function clearAssetCache(): bool
    {
        return Cache::forget($this->getAssetCacheKey());
    }

    private function getAssetCacheKey():string
    {
        return "game:{$this->id}:asset_images";
    }

    public function getTypeIdentifierAttribute(): string
    {
        return Cache::remember("game_type:{$this->game_type_id}:identifier", now()->addMinutes(90), function() {
            return $this->type->identifier;
        });
    }

    public function getTypeRequiredAssetsAttribute() {
        $win_levels = $this->win_levels;
        return Cache::remember("game_type:{$this->game_type_id}:required_asset_identifiers:{$win_levels}", now()->addMinutes(90), function() use ($win_levels) {
            return $this->type->getRequiredAssetsByWinLevel($win_levels);
        });
    }

    /**
     * Get whether the game is ready for approval
     */
    public function getCanApproveAttribute():bool
    {
        return count($this->getAssetImages()) >= count($this->type_required_assets) && $this->rewards()->count() >= $this->win_levels;
    }

}
