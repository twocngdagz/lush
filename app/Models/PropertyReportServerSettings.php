<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;

class PropertyReportServerSettings extends Model
{
    use LogsAllActivity;

    protected $table = 'property_report_servers';
    protected $guarded = [];

    public static function findByPropertyId($property_id)
    {
        return self::where('property_id', '=', $property_id)->first() ?? new static;
    }
}
