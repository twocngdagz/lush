<?php

namespace App\Casts;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;

class CarbonCast implements \Spatie\LaravelData\Casts\Cast
{

    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        if ($value === null) {
            return Uncastable::create();
        }
        return Carbon::parse($value);

    }
}
