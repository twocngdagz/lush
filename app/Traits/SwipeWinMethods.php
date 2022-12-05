<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait SwipeWinMethods {
    /**
     * SwipeWin Relationship
     *
     * @return SwipeWin
     **/
    public function swipeWin(): HasOne
    {
        return $this->hasOne(SwipeWin::class);
    }
}
