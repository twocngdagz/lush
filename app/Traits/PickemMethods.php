<?php

namespace App\Traits;

use App\Models\Season as PickemSeason;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait PickemMethods {

    // /**
    //  * Get weeks for this promotion
    //  *
    //  * @return Collection
    //  **/
    // public static function weeks()
    // {
    // 	// return $this->hasMany(PickemWeek::class);
    // 	// return self::where('identifier', $identifier)->firstOrFail();
    // }

    /**
     * Promotion season for this pickem promotion
     *
     * @return \App\Pickem\Season
     **/
    public function season(): HasOne
    {
        return $this->hasOne(PickemSeason::class);
    }
}
