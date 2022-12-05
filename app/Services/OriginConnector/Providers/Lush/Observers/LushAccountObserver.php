<?php

namespace App\Services\OriginConnector\Providers\Lush\Observers;

use App\Services\OriginConnector\Providers\Lush\Models\LushAccount;

class LushAccountObserver
{
    /**
     * Handle the account "creating" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushAccount; $account
     * @return void
     */
    public function creating(LushAccount $lushAccount): void
    {
        $lushAccount->is_currency = in_array($lushAccount->type, ['comps', 'promo', 'comps_earned_today', 'promo_earned_today']);
    }

    /**
     * Handle the lush account "created" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushAccount;  $lushAccount
     * @return void
     */
    public function created(LushAccount $lushAccount): void
    {
        //
    }

    /**
     * Handle the lush account "updated" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushAccount;  $lushAccount
     * @return void
     */
    public function updated(LushAccount $lushAccount): void
    {
        if ($lushAccount->type === 'points') {
            LushAccount::where('type', 'points_earned_today')
                ->where('lush_player_id', $lushAccount->lush_player_id)
                ->increment('balance', $lushAccount->getAttributes()['balance'] - $lushAccount->getOriginal('balance'));
        }

        if ($lushAccount->type === 'comps') {
            LushAccount::where('type', 'comps_earned_today')
                ->where('lush_player_id', $lushAccount->lush_player_id)
                ->increment('balance', $lushAccount->getAttributes()['balance'] - $lushAccount->getOriginal('balance'));
        }

        if ($lushAccount->type === 'promo') {
            LushAccount::where('type', 'promo_earned_today')
                ->where('lush_player_id', $lushAccount->lush_player_id)
                ->increment('balance', $lushAccount->getAttributes()['balance'] - $lushAccount->getOriginal('balance'));
        }
    }

    /**
     * Handle the lush account "deleted" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushAccount;  $lushAccount
     * @return void
     */
    public function deleted(LushAccount $lushAccount): void
    {
        //
    }

    /**
     * Handle the lush account "restored" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushAccount;  $lushAccount
     * @return void
     */
    public function restored(LushAccount $lushAccount): void
    {
        //
    }

    /**
     * Handle the lush account "force deleted" event.
     *
     * @param  \App\Services\OriginConnector\Providers\Lush\Models\LushAccount;  $lushAccount
     * @return void
     */
    public function forceDeleted(LushAccount $lushAccount): void
    {
        //
    }
}
