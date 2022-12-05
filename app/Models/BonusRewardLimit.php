<?php

namespace App\Models;

use App\Models\Bonus;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Origin;
use PhpParser\Builder;

class BonusRewardLimit extends Model
{
    use LogsAllActivity;
    protected $guarded = [];

    /**
     * Bonus relationship
     */
    public function bonus(): BelongsTo
    {
        return $this->belongsTo(Bonus::class);
    }

    /**
     * Getter for rank name
     * @param string $default
     * @return string
     */
    public function getRankName(string $default = '-'): string
    {
        return Origin::getPropertyRanks()->keyBy('id')->get($this->rank_id)->name ?? $default;
    }

    /**
     * Scopes query to retrieve all reward limits where rank_id is null or matches specified rank_id
     * @param Builder $query
     * @param int $rank_id
     * @return Builder
     */
    public function scopeOfRank(Builder $query, int $rank_id): Builder
    {
        $query
            ->whereNull('rank_id')
            ->orWhere('rank_id', '=', $rank_id);
    }
}
