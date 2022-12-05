<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use League\Fractal\TransformerAbstract;

class PropertyInfoTransformer extends TransformerAbstract
{

    public function transform($property)
    {
        return [
            'id' => $property->id,
            'name' => $property->name,
            'timezone' => $property->timezone,
        ];
    }
}
