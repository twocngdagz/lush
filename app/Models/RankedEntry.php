<?php

namespace App\Models\Pickem;

use Illuminate\Database\Eloquent\Model;

class RankedEntry extends Model
{
    protected $table = 'pickem_ranked_entries';
    protected $guarded = [];
    public $timestamps = false;
}
