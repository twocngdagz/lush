<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string callback_response_url
 * @property string status
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Kiosk|null kiosk
 */
class ScanTrigger extends Model
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCEEDED = 'SUCCEEDED';
    const STATUS_FAILED = 'FAILED';

    protected $fillable = ['status'];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(Kiosk::class);
    }

    public function getCallbackResponseUrlAttribute(): string
    {
        return route('api.v1.scanTriggers.update', $this);
    }
}
