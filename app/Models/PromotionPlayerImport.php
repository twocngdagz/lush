<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionPlayerImport extends Model
{

    protected $fillable = ['job_name', 'file_name', 'promotion_id', 'found', 'created', 'duplicates', 'invalid', 'status', 'canceled_at'];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function scopeProcessing($query): Builder
    {
        return $query->whereIn('status', ['created', 'processing']);
    }

    public function scopeFinished($query): Builder
    {
        return $query->whereIn('status', ['completed', 'failed', 'canceled']);
    }

    public function scopeCompleted($query): Builder
    {
        return $query->where('status', '=', 'completed');
    }

    public function scopeFailed($query): Builder
    {
        return $query->where('status', '=', 'failed');
    }
}
