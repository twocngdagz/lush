<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use League\Fractal\TransformerAbstract;

class TrackingSessionTransformer extends TransformerAbstract
{

    public function transform($session)
    {
        return [
            'id' => $session->id,
            'type' => $session->play_type,
            'start_at' => $session->starts_at,
            'end_at' => $session->ends_at,
            'gaming_date' => $session->gaming_date,
            'time_played' => $session->seconds_played * 60,
            'points_earned' => $session->points_earned,
            'cash_in' => $session->cash_in,
            'theo_win' => $session->theo_win,
            'actual_win' => $session->actual_win,
            'comp_earned' => $session->comp_earned,
        ];
    }
}
