<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    protected $fillable = ['data', 'event_log_type_id', 'kiosk_id'];

    /**
     * Event Log Type relationship
     *
     * @return BelongsTo
     **/
    public function type(): BelongsTo
    {
        return $this->belongsTo('EventLogType', 'event_log_type_id');
    }

    /**
     * Event Log Kiosk relationship
     *
     * @return BelongsTo
     **/
    public function kiosk(): BelongsTo
    {
        return $this->belongsTo('Kiosk');
    }

    /**
     * Get all events that happened within the past week
     *
     */
    public function scopeEventsToday($q)
    {
        return $q->where('created_at', '>', Carbon::now()->startOfDay());
    }

    /**
     * Get all events that happened within the past week
     * @return Relationship
     */
    public function scopeEventsThisWeek($q)
    {
        return $q->where('created_at', '>', Carbon::now()->subWeeks(1));
    }

    /**
     * Get all events that happened within the past month
     * @return Relationship
     */
    public function scopeEventsThisMonth($q)
    {
        return $q->where('created_at', '>', Carbon::now()->subMonths(1));
    }

    /**
     * Get all events that happened within the past year
     * @return Relationship
     */
    public function scopeEventsThisYear($q)
    {
        return $q->where('created_at', '>', Carbon::now()->subYear(1));
    }

    /**
     * Log a new event
     * @param  EventLogType $eventType Event type to log
     * @param  Kiosk     $kiosk     The kiosk this event is happening on
     * @param  array     $data      Any other data to store
     * @return EventLog
     */
    public static function log(EventLogType $eventType, Kiosk $kiosk, array $data): EventLog
    {
        self::create([
            'data' => json_encode($data),
            'event_log_type_id' => $eventType->id,
            'kiosk_id' => $kiosk->id
        ]);
    }
}
