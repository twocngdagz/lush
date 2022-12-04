<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BounceBackPromotion extends Model
{
    protected $fillable = [
        'promotion_id',
        'previous_promotion_id',
        'redemption_starts_at',
        'redemption_ends_at'
    ];

    protected $dates = ['redemption_starts_at', 'redemption_ends_at', 'created_at', 'updated_at'];

    /**
     * Promotion relationship
     *
     **/
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Previous Promotion relationship
     *
     **/
    public function previousPromotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'previous_promotion_id');
    }
}
