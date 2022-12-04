<?php

namespace App\Models;

use App\Services\OriginConnector\Providers\Oasis\v12\nConnect\v1\Models\PropertyRedemptionAccountSettings;
use App\Traits\LogsAllActivity;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Property extends Model
{
    use LogsAllActivity;

    protected $guarded = [];
    protected $appends = ['display_name', 'lush_apps'];

    public function getDisplayNameAttribute(): ?string
    {
        return ($this->property_code && $this->name) ? $this->property_code . ' - ' . $this->name : null;
    }

    public function getBadgeImageUrlAttribute()
    {
        return Cache::remember("property:{$this->id}:badge_image_url", now()->addMinutes(5), function () {
            return $this->kioskSettings->badge_image_url;
        });
    }

    /**
     * Kiosk Settings for property
     *
     * @return HasOne
     */
    public function kioskSettings(): HasOne
    {
        return $this->hasOne(PropertyKioskSettings::class)->withDefault();
    }

    /**
     * Kiosk Messages for property
     *
     * @return HasOne
     */
    public function kioskMessages(): HasOne
    {
        return $this->hasOne(PropertyKioskMessages::class)->withDefault();
    }

    /**
     * connectorSettings attribute
     *
     * @return PropertyConnectorSettings
     */
    public function getConnectorSettingsAttribute(): PropertyConnectorSettings
    {
        return PropertyConnectorSettings::findByPropertyId($this->id);
    }

    public function reportServerSettings(): HasOne
    {
        return $this->hasOne(PropertyReportServerSettings::class)->withDefault();
    }

    public function mailServerSettings(): HasOne
    {
        return $this->hasOne(PropertyMailServerSettings::class)->withDefault();
    }

    public function oneLinkSettings(): HasOne
    {
        return $this->hasOne(PropertyOneLinkSettings::class)->withDefault();
    }

    public function hotelSettings()
    {
        return $this->hasOne(PropertyHotelSettings::class)->withDefault();
    }

    public function printers(): HasMany
    {
        return $this->hasMany(TicketPrinter::class);
    }

    public function pokerRatings(): HasMany
    {
        return $this->hasMany(PropertyPokerRating::class);
    }

    public static function findByCode($code)
    {
        if (is_array($code) || $code instanceof Arrayable) {
            return self::whereIn('property_code', $code)->get();
        }

        return self::where('property_code', '=', $code)->first();
    }

    public function getBalanceDisplayOptionsAttribute(): Collection
    {
        $default = collect(\Origin::balanceDisplayOptions());
        $saved = collect([]);
        if (isset($this->attributes['balance_display_options'])) {
            $saved = collect(json_decode($this->attributes['balance_display_options'], true) ?? []);
        }

        return $default->map(function ($default_options) use ($saved) {
            return array_merge($default_options, $saved->firstWhere('identifier', $default_options['identifier']) ?? []);
        });
    }

    public function getLushAppsAttribute()
    {
        return arrayKeysKabobToSnake(config('licenses.lush-apps'));
    }

    public function balanceDisplayOptionsFor($name)
    {
        return $this->balance_display_options->keyBy('identifier')->get($name, null);
    }

    public function getCustomMessagesAttribute() {
        return $this->kioskMessages->messages;
    }

    /**
     * propertyRedemptionAccountSettings attribute
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|PropertyRedemptionAccountSettings
     */
    public function redemptionAccountSettings(): HasOne|PropertyRedemptionAccountSettings
    {
        return $this->hasOne(PropertyRedemptionAccountSettings::class)->latest()->withDefault();
    }

    /**
     * Ticket Printer relationship for property
     *
     * @return HasMany
     */
    public function ticketPrinters(): HasMany
    {
        return $this->hasMany(TicketPrinter::class, 'property_id', 'id');
    }

    /**
     * Enable audio playback on the kiosks?
     *
     * @return boolean
     */
    public function getEnableKioskAudioAttribute(): bool
    {
        return config('audio.enable_kiosk_audio', false);
    }


}
