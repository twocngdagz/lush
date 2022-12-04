<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRankExclusion extends Model
{
    protected $fillable = ['account_id', 'ext_rank_id', 'ext_rank_name'];
}
