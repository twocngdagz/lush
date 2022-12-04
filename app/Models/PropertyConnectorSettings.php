<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;

class PropertyConnectorSettings extends Model
{
    use LogsAllActivity;

    protected $table = 'property_connector_settings';
    protected $guarded = [];
    protected $casts = [
        'settings' => 'array'
    ];

    public static function findByPropertyId($property_id)
    {
        return self::where('property_id', '=', $property_id)->orderBy('created_at', 'desc')->first() ?? new static;
    }
}
