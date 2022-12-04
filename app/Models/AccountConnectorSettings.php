<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountConnectorSettings extends Model
{
    protected $table = 'account_connector_settings';
    protected $guarded = [];
    protected $casts = [
        'settings' => 'array'
    ];

    public static function findByAccountId($account_id)
    {
        return self::where('account_id', '=', $account_id)->orderBy('created_at', 'desc')->first() ?? new static;
    }
}
