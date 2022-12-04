<?php

namespace App\Models;

use App\Traits\LogsAllActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KioskCardPrinter extends Model
{
    protected $casts = [
        'hoppers' => 'array',
    ];
    use LogsAllActivity;

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
