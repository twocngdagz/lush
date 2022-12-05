<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;

class PromotionRank extends Model
{
    use LogsAllActivity;

    protected $fillable = ['ext_rank_id'];
}
