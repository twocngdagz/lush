<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Contains the necessary details for printing Loyalty Cards.
 *
 * @property string kiosk_id
 * @property string track_1
 * @property string track_2
 * @property string last_name
 * @property string first_name
 * @property string player_id
 * @property string expiration
 * @property string print_layout
 * @property string action_type
 */
class CardPrintDetail extends Model
{
    public $guarded = [];
    public $timestamps = true;
    protected $dates = ['created_at', 'updated_at'];

    use SoftDeletes;

    /**
     * Kiosk relationship
     *
     * @return BelongsTo;
     **/
    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(Kiosk::class);
    }

}
