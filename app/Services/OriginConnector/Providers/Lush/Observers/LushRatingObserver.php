<?php

namespace App\Services\OriginConnector\Providers\Lush\Observers;

use App\Services\OriginConnector\Providers\Lush\Models\LushAccount;
use App\Services\OriginConnector\Providers\Lush\Models\LushRating;

class LushRatingObserver
{
    /**
     * Handle the lush rating "created" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushRating  $lushRating
     * @return void
     */
    public function created(LushRating $lushRating)
    {
        if ($lushRating->points_earned) {
            $account = LushAccount::where('type', 'points')
                ->where('lush_player_id', $lushRating->lush_player_id)
                ->first();

            $account->update(['balance' => $account->balance + $lushRating->points_earned]);
        }

        if ($lushRating->comp_earned) {
            $account = LushAccount::where('type', 'comps')
                ->where('lush_player_id', $lushRating->lush_player_id)
                ->first();

            $account->update(['balance' => $account->balance + $lushRating->comp_earned]);
        }
    }

    /**
     * Handle the lush rating "updated" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushRating  $lushRating
     * @return void
     */
    public function updated(LushRating $lushRating)
    {
        //
    }

    /**
     * Handle the lush rating "deleted" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushRating  $lushRating
     * @return void
     */
    public function deleted(LushRating $lushRating)
    {
        //
    }

    /**
     * Handle the lush rating "restored" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushRating  $lushRating
     * @return void
     */
    public function restored(LushRating $lushRating)
    {
        //
    }

    /**
     * Handle the lush rating "force deleted" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushRating  $lushRating
     * @return void
     */
    public function forceDeleted(LushRating $lushRating)
    {
        //
    }
}
