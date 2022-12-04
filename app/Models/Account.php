<?php

namespace App\Models;

use App\Services\RealWinSolution\Models\RealWinConnection;
use App\Traits\LogsAllActivity;
use App\Traits\UsesModelIdentifier;
use App\Services\OriginConnector\Facades\OriginFacade as Origin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use UsesModelIdentifier;
    use LogsAllActivity;

    protected $guarded = [];
    protected $hidden = ['connection_test_player_id'];

    /**
     * Kiosk relationship for accounts
     *
     * @return HasMany
     **/
    public function kiosks(): HasMany
    {
        return $this->hasMany(Kiosk::class);
    }

    /**
     * All promotions for the account
     *
     * @return HasMany
     */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Properties relationship
     *
     * @return HasMany
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * connectorSettings attribute
     *
     * @return HasOne
     */
    public function connectorSettings(): HasOne
    {
        return $this->hasOne(AccountConnectorSettings::class)->latest()->withDefault();
    }

    public function realWinConnectorSettings(): HasOne
    {
        return $this->hasOne(RealWinConnection::class)->latest();
    }

    /**
     * Rank exclusions relationship
     *
     * @return HasMany
     */
    public function rankExclusions(): HasMany
    {
        return $this->hasMany(AccountRankExclusion::class);
    }

    /**
     * The account's points per dollar setting
     *
     * @return float|string
     */
    public function getPointsPerDollarAttribute(): float|int|string
    {
        if (appFeatures('setting.points-per-dollar')) {
            return Origin::getPropertyPointsPerDollar();
        }

        return $this->attributes['points_per_dollar'] ?? 1;
    }

    /**
     * The account's comps per dollar setting
     *
     * @return float|string
     */
    public function getCompsPerDollarAttribute(): float|int|string
    {
        if (appFeatures('setting.comps-per-dollar')) {
            return Origin::getPropertyCompsPerDollar();
        }

        return $this->attributes['comps_per_dollar'] ?? 1;
    }

}
