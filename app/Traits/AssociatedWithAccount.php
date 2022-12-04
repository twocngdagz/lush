<?php

namespace App\Traits;

trait AssociatedWithAccount {

    /**
     * Register any observers
     *
     * @return void
     **/
    public static function bootAssociatedWithAccount(): void
    {
        static::creating(function($model){
            if( ! $model->account_id && isset(auth()->user()->account->id)) {
                $model->account_id = auth()->user()->account->id;
            }
        });
    }

    /**
     * Get a model record based on the account
     *
     * @return void
     **/
    public static function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope for accounts with active status
     *
     * @return void
     **/
    public static function scopeActiveForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId)->orderBy('index')->orderBy('created_at', 'desc')->where('active', true);
    }
}
