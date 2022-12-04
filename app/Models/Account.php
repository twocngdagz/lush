<?php

namespace App\Models;

use App\Services\RealWinSolution\Models\RealWinConnection;
use App\Traits\LogsAllActivity;
use App\Traits\UsesModelIdentifier;
use App\Models\Account\AccountRankExclusion;
use App\Models\Account\AccountConnectorSettings;
use App\Services\OriginConnector\Facades\OriginFacade as Origin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * connectorSettings attribute
     *
     * @return AccountConnectorSettings
     */
    public function connectorSettings()
    {
        return $this->hasOne(AccountConnectorSettings::class)->latest()->withDefault();
    }

    public function realWinConnectorSettings()
    {
        return $this->hasOne(RealWinConnection::class)->latest();
    }

    /**
     * Rank exclusions relationship
     *
     * @return HasMany
     */
    public function rankExclusions()
    {
        return $this->hasMany(AccountRankExclusion::class);
    }

    /**
     * The account's points per dollar setting
     *
     * @return float|string
     */
    public function getPointsPerDollarAttribute()
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
    public function getCompsPerDollarAttribute()
    {
        if (appFeatures('setting.comps-per-dollar')) {
            return Origin::getPropertyCompsPerDollar();
        }

        return $this->attributes['comps_per_dollar'] ?? 1;
    }

}
