<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use League\Fractal\TransformerAbstract;

class PropertyRankTransformer extends TransformerAbstract
{

    public function transform($rank)
    {
        return [
            'id' => $rank->id,
            'name' => $rank->name,
            'points_threshold' => $rank->threshold,
            'points_multiplier' => null,
            'comps_multiplier' => null,
            'sort_order' => $rank->threshold,
        ];
    }
}
