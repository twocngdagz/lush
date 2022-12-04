<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;

trait LogsAllActivity
{
    use LogsActivity;

    // Don't log activity if only updated_at changed
    // Need to make sure we are logging what
    // caused the updated_at date to change
    // Most likely a relationship
    protected static $ignoreChangedAttributes = ['updated_at'];

    // Log all attributes
    protected static $logAttributes = ['*'];

    // Ignore the updated_at and created_at attributes
    protected static $logAttributesToIgnore = ['updated_at', 'created_at'];

    // Only log the attributes that were changed
    protected static $logOnlyDirty = true;

    // Does not save empty logs.
    protected static $submitEmptyLogs = false;
}
