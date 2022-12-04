<?php

namespace App\Models;

use App\Services\RealWinSolution\RealWinSolution;
use Origin;
use Illuminate\Database\Eloquent\Model;

class EarningMethodType extends Model
{

    protected $guarded = [];

    public $timestamps = false;


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        $realWinSolution = app(RealWinSolution::class);
        static::addGlobalScope(
            'connectorSupportedEarningMethodType',
            function ($query) use ($realWinSolution) {
                $methods = collect(appFeatures('global.earning-methods'))->filter()->keys();
                $ratings = collect(appFeatures('global.earning-ratings'))->filter()->keys();

                $types = $methods->crossJoin($ratings)->map(
                    function ($item) {
                        if (! in_array('other', $item)) {
                            return implode('-', $item);
                        }
                    }
                )->filter(
                    function ($item) {
                        return $item;
                    }
                )->merge($realWinSolution->getRatings());

                $query->whereIn('identifier', $types->toArray());
            }
        );

    }//end boot()


    public static function idFromIdentifier($identifier)
    {
        return self::where('identifier', '=', $identifier)->first()->id ?? null;

    }//end idFromIdentifier()


}//end class
