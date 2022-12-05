<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionEarningSetting extends Model
{
    protected $guarded = [];
    protected $appends = [];

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
}
