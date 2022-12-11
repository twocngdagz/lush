<?php

namespace App\Models;

use App\Traits\EarnsRewards;
use Database\Factories\BonusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bonus extends Model
{
    /**
     * The EarnsRewards trait adds reward functionality for
     */
    use EarnsRewards;
    use LogsAllActivity;
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'bonus_multiplier_bucket_id',
        'bonus_multiplier_value',
        'starts_at',
        'ends_at'
    ];
    protected $dates = ['starts_at', 'ends_at', 'created_at', 'updated_at'];

    public static function newFactory(): BonusFactory
    {
        return BonusFactory::new();
    }

    /**
     * Reward limit relationship
     *
     * @return HasMany
     */
    public function rewardLimits(): HasMany
    {
        return $this->hasMany(BonusRewardLimit::class);
    }

    /**
     * Bonus Multiplier Bucket Type
     *
     **/
    public function bonusMultiplierBucketType(): HasOne
    {
        return $this->hasOne(RewardType::class, 'id', 'bonus_multiplier_bucket_id');
    }

    /**
     * Promotion relationship
     *
     **/
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Event relationship
     * @return HasMany
     */
    public function rewardPeriods(): HasMany
    {
        return $this->hasMany(BonusRewardPeriod::class)->orderBy('starts_at');
    }
}
