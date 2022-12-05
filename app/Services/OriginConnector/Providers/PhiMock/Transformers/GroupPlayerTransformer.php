<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class GroupPlayerTransformer extends TransformerAbstract
{

    public function transform($groupPlayer)
    {
        return [
            'id' => $groupPlayer->id,
            'ext_id' => $groupPlayer->id,
            'name' => $groupPlayer->name
        ];
    }
}
