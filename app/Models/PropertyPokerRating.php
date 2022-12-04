<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;

class PropertyPokerRating extends Model
{
    use LogsAllActivity;

    protected $fillable = ['origin_game_code', 'property_id'];
}
