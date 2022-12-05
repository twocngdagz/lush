<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardType extends Model
{
    use LogsAllActivity;

    protected $guarded = [];
    public $timestamps = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('connectorSupportedRewardType', function ($query) {
            $types = collect(appFeatures('promotion.all.reward-types'))->filter()->keys();

            $query->whereIn('identifier', $types);
        });
    }

    /**
     * All the rewards for a single type
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public static function idFromIdentifier($identifier): ?int
    {
        return self::where('identifier', '=', $identifier)->first()->id ?? null;
    }
}
