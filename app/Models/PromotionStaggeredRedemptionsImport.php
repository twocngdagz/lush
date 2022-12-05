<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionStaggeredRedemptionsImport extends Model
{
    use LogsAllActivity;

    protected $table = 'promotion_staggered_redemptions';

    public $guarded = [];
    public $timestamps = true;

    protected $fillable = ['promotion_id', 'earn_win_reward_period_id' ,'player_id', 'entry_start_date', 'entry_start_time', 'entry_end_date', 'entry_end_time'];

    /**
     * Player relationship
     * @return BelongsTo
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

}
