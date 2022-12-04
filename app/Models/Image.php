<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $appends = ['uri', 'url'];

    /**
     * On boot
     *
     * @return void
     **/
    protected static function boot()
    {
        parent::boot();

        /**
         * When you delete an image it will automatically attempt
         * to remove the physical image
         */
        static::deleting(function($image){
            Storage::disk('public')->delete($image->path);
        });
    }

    /**
     * Return the relative URI for the front end
     *
     * @return string
     **/
    public function getUriAttribute()
    {
        return '/assets/uploads/'.$this->path;
    }

    /**
     * Get the full URL for the image
     *
     * @return string
     **/
    public function getUrlAttribute()
    {
        return config('app.url').$this->uri;
    }
}
