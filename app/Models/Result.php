<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'pickem_results';

    /**
     * Winners of the pickem week relationship
     *
     * @return Builder
     **/
    public function winners()
    {
        return $this->hasMany(Winner::class, 'result_id');
    }

    /**
     * Week relationship
     *
     * @return Week
     **/
    public function week()
    {
        return $this->belongsTo(Week::class);
    }
}
