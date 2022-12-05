<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PlayerGroupTransformer extends TransformerAbstract
{

    public function transform($group)
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'active' => true,
            'start_at' => $group->starts_at,
            'end_at' => $group->ends_at
        ];
    }
}
