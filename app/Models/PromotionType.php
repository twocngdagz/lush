<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ConnectorSupportedPromotionTypeScope;

class PromotionType extends Model
{
    protected $fillable = [
        'name',
        'identifier',
        'sort',
        'description'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('connectorSupportedPromotionTypeScope', function ($query) {
            $types = collect(appFeatures('promotion.type'))->filter()->keys();
            $query->whereIn('identifier', $types)->orderBy('sort');
        });
    }

}
