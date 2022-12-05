<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPromotionAdditionalInfo extends Model
{
    public $table = "static_promotion_additional_info";
    protected $fillable = ['promotion_id', 'additional_info'];
    public $timestamps = true;
}
