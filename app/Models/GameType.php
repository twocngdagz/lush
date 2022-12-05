<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameType extends Model
{
    protected $fillable = ['name', 'identifier', 'sort', 'description', 'min_win_levels', 'max_win_levels'];

    /**
     * Game type assets
     */
    public function assets(): HasMany
    {
        return $this->hasMany(GameTypeAsset::class);
    }

    /**
     * Filter out assets that will be not required for the games configured number of win levels.
     */
    public function getRequiredAssetsByWinLevel($win_levels)
    {
        $assets_to_ignore = [];
        foreach ($this->assets as $asset) {
            if (is_numeric(substr($asset->identifier, -2)) and (int) substr($asset->identifier, -2) > $win_levels) {
                $assets_to_ignore[] = $asset->identifier;
            }
        }

        return array_diff($this->required_assets, $assets_to_ignore);
    }

    public function getRequiredAssetsAttribute(): array
    {
        return $this->assets->pluck('identifier')->toArray();
    }
}


