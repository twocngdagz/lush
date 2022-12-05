<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PropertyGroupTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'players'
    ];

    public function transform($group)
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'enrolled_count' => $group->players_count,
            'start_at' => $group->starts_at,
            'end_at' => $group->ends_at
        ];
    }

    /**
     * Include group players as needed
     *
     * @return Collection
     **/
    public function includePlayers($group)
    {
        if (isset($group->players)) {
            return $this->collection($group->players, new GroupPlayerTransformer);
        }
    }
}
