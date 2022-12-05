<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingFreeEntryByCriteria extends Model
{
    protected $table = 'drawing_free_entries_by_criteria';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * Drawing relationship
     * @return BelongsTo
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }
}
