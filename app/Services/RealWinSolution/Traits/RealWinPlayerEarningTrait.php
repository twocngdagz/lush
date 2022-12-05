<?php

namespace App\Services\RealWinSolution\Traits;

use App\PromotionEarningMethod;
use App\Services\RealWinSolution\RealWinSolution;
use Illuminate\Support\Str;

trait RealWinPlayerEarningTrait
{
    public function __call($name, $arguments)
    {
        if (Str::contains($name, 'Other')) {
            $realWinSolution = app(RealWinSolution::class);
            [$playerId] = $arguments;
            $earningMethod = last(array_filter($arguments));

            if (! $earningMethod instanceof PromotionEarningMethod) {
                $earningMethod = json_decode($earningMethod);
            }
            $score = (float) $realWinSolution->getPlayerScore($playerId);
            
            return $score >= $earningMethod->min_score and $score <= $earningMethod->max_score;
        }
    }
}