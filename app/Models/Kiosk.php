<?php

namespace App\Models;

use App\Property;
use App\Models\Kiosk\ScanTrigger;
use App\Traits\UsesModelIdentifier;
use App\Traits\AssociatedWithAccount;

use Carbon\Carbon;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Model;

class Kiosk extends Model
{
    use LogsAllActivity;
    use UsesModelIdentifier;
    use AssociatedWithAccount;

    protected $fillable = ['name', 'description', 'identifier', 'account_id', 'property_id', 'show_lost_card_button', 'show_player_enrollment_button'];

    /**
     * Boot this model
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('forUser', function ($query) {
            if (!auth()->user() || auth()->user()->isA('super-admin')) {
                return;
            }

            $query->whereIn('property_id', auth()->user()->property_ids);
        });
    }

    /**
     * Account relationship for kiosk
     *
     * @return App\Account
     **/
    public function account(): App\Account
    {
        return $this->belongsTo(\App\Account::class);
    }

    /**
     * Property Relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Ticket Printer relationship for kiosk
     *
     * @return App\TicketPrinter
     */
    public function ticketPrinter()
    {
        return $this->hasOne(\App\TicketPrinter::class);
    }

    /**
     * Card printer relationship for kiosk
     *
     * @return App\KioskCardPrinter
     **/
    public function cardPrinter()
    {
        return $this->hasOne(\App\KioskCardPrinter::class);
    }

    /**
     * ID Document Scanner relationship for kiosk
     *
     **/
    public function documentScanner()
    {
        return $this->hasOne(\App\KioskDocumentScanner::class);
    }

    /**
     * Event log relationship for kiosk
     *
     * @return Collection
     **/
    public function events()
    {
        return $this->hasMany(\App\EventLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all events that happened within the past week
     * @return Relationship
     */
    public function eventsToday()
    {
        return $this->events()->where('created_at', '>', Carbon::now()->startOfDay());
    }

    /**
     * Get all events that happened within the past week
     * @return Relationship
     */
    public function eventsThisWeek()
    {
        return $this->events()->where('created_at', '>', Carbon::now()->subWeeks(1));
    }

    /**
     * Get all events that happened within the past month
     * @return Relationship
     */
    public function eventsThisMonth()
    {
        return $this->events()->where('created_at', '>', Carbon::now()->subMonths(1));
    }

    /**
     * Assign the kiosk URL
     *
     * @return string
     **/
    public function getKioskUrlAttribute()
    {
        if (empty($this->property->app_kiosk_url)) {
            throw new \Exception("Missing kiosk URL in property settings for property {$this->property->property_code} - {$this->property->name}.");
        }
        return "{$this->property->app_kiosk_url}?kiosk={$this->identifier}";
    }

    /**
     * Quick properties check to see if we can edit this promotion
     **/
    public function getCanEditAttribute()
    {
        if (!auth()->user()) return false;
        else if (auth()->user()->isA('super-admin')) return true;

        return in_array($this->property_id, auth()->user()->property_ids);
    }

    public function scanTriggers()
    {
        return $this->hasMany(ScanTrigger::class);
    }
}
