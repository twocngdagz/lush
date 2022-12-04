<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KioskDocumentScanner extends Model
{
    use LogsAllActivity;

    protected $guarded = [];

    /**
     * Kiosk relationship
     *
     * @return BelongsTo;
     **/
    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(Kiosk::class);
    }

    /**
     * ID Document Scanner Type relationship
     *
     * @return HasOne
     */
    public function IdDocumentScannerType(): HasOne
    {
        return $this->hasOne(IdDocumentScannerType::class, 'id', 'id_document_scanner_type_id');
    }
}
