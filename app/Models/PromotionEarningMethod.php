<?php

namespace App\Models;


use App\Traits\LogsAllActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PromotionEarningMethod extends Model
{
    use LogsAllActivity;

    protected $guarded = [];
    protected $appends = ['identifier', 'rating'];

    /**
     * Promotion relationship for the earning settings
     *
     * @return BelongsTo
     **/
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * The type relationship
     *
     * @return BelongsTo
     **/
    public function type(): BelongsTo
    {
        return $this->belongsTo(EarningMethodType::class, 'earning_method_type_id');
    }

    public function getIdentifierAttribute() {
        return Cache::remember("earning_method_type:{$this->earning_method_type_id}:identifier", now()->addMinutes(90), function() {
            return $this->type->identifier;
        });
    }

    public function getRatingAttribute(): string
    {
        $parts = explode(' ', $this->type->name);
        if (isset($parts[2]) and $parts[1] == '+') { // 'Pit + Slot' combination type rating
            return implode(' ', [$parts[0], $parts[1], $parts[2]]);
        } else { // single type rating
            return explode(' ', $this->type->name)[0];
        }
    }
}
