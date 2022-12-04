<?php

namespace App\Models;

use App\Traits\UsesModelIdentifier;
use App\Traits\AssociatedWithAccount;

use Carbon\Carbon;
use App\Traits\LogsAllActivity;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * @return BelongsTo
     **/
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Property Relationship
     * @return BelongsTo
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Ticket Printer relationship for kiosk
     *
     * @return HasOne
     */
    public function ticketPrinter(): HasOne
    {
        return $this->hasOne(TicketPrinter::class);
    }

    /**
     * Card printer relationship for kiosk
     *
     * @return HasOne
     **/
    public function cardPrinter(): HasOne
    {
        return $this->hasOne(KioskCardPrinter::class);
    }

    /**
     * ID Document Scanner relationship for kiosk
     *
     **/
    public function documentScanner(): HasOne
    {
        return $this->hasOne(KioskDocumentScanner::class);
    }

    /**
     * Event log relationship for kiosk
     *
     * @return HasMany
     **/
    public function events(): HasMany
    {
        return $this->hasMany(EventLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all events that happened within the past week
     *
     */
    public function eventsToday(): Builder
    {
        return $this->events()->where('created_at', '>', Carbon::now()->startOfDay());
    }

    /**
     * Get all events that happened within the past week
     *
     */
    public function eventsThisWeek(): Builder
    {
        return $this->events()->where('created_at', '>', Carbon::now()->subWeeks(1));
    }

    /**
     * Get all events that happened within the past month
     *
     */
    public function eventsThisMonth(): Builder
    {
        return $this->events()->where('created_at', '>', Carbon::now()->subMonths(1));
    }

    /**
     * Assign the kiosk URL
     *
     * @return string
     **/
    public function getKioskUrlAttribute(): string
    {
        return empty($this->property->app_kiosk_url) ? throw new Exception("Missing kiosk URL in property settings for property {$this->property->property_code} - {$this->property->name}.") : "{$this->property->app_kiosk_url}?kiosk={$this->identifier}";
    }

    /**
     * Quick properties check to see if we can edit this promotion
     **/
    public function getCanEditAttribute(): bool
    {
        if (!auth()->user()) return false;
        else if (auth()->user()->isA('super-admin')) return true;

        return in_array($this->property_id, auth()->user()->property_ids);
    }

    public function scanTriggers(): HasMany
    {
        return $this->hasMany(ScanTrigger::class);
    }
}
