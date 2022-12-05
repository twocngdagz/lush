<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class TableGameTransformer extends TransformerAbstract
{

    public function transform($tableGame)
    {
        return [
            'active' => $tableGame->GameActive,
            'game_code' => $tableGame->GameType,
            'game_name' => $tableGame->GameTypeName,
        ];
    }
}
