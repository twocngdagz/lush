<?php

namespace App\Services\OriginConnector\Providers\Lush\Observers;

use App\Services\OriginConnector\Providers\Lush\Models\LushPlayer;

class LushPlayerObserver
{
    /**
     * Handle the lush player "created" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushPlayer  $lushPlayer
     * @return void
     */
    public function created(LushPlayer $lushPlayer)
    {
        $lushPlayer->lushaccounts()->createMany(    [
            ['type' => 'points'],
            ['type' => 'comps'],
            ['type' => 'promo'],
            ['type' => 'points_earned_today'],
            ['type' => 'comps_earned_today'],
            ['type' => 'promo_earned_today'],
        ]);
    }

    /**
     * Handle the lush player "updated" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushPlayer  $lushPlayer
     * @return void
     */
    public function updated(LushPlayer $lushPlayer)
    {
        //
    }

    /**
     * Handle the lush player "deleted" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushPlayer  $lushPlayer
     * @return void
     */
    public function deleted(LushPlayer $lushPlayer)
    {
        //
    }

    /**
     * Handle the lush player "restored" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushPlayer  $lushPlayer
     * @return void
     */
    public function restored(LushPlayer $lushPlayer)
    {
        //
    }

    /**
     * Handle the lush player "force deleted" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushPlayer  $lushPlayer
     * @return void
     */
    public function forceDeleted(LushPlayer $lushPlayer)
    {
        //
    }
}
