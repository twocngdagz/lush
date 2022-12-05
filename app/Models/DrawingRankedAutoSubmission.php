<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawingRankedAutoSubmission extends Model
{
    protected $guarded = [];
    protected $casts = [
        'free' => 'boolean',
        'earned' => 'boolean',
    ];
}
