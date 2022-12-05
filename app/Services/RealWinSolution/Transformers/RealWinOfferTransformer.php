<?php

namespace App\Services\RealWinSolution\Transformers;

use Illuminate\Support\Carbon;
use League\Fractal\TransformerAbstract;

class RealWinOfferTransformer extends TransformerAbstract
{
    public function transform($offer)
    {
        return [
            'id' => $offer['id'],
            'issuedAmount' => null,
            'offerName' => $offer['name'],
            'offerDescription' => $offer['description'],
            'offerType' => $offer['offer_type'],
            'redeemedAmount' => null,
            'offerAmount' => $offer['amount'],
            'availableStartDateLabel' => Carbon::parse($offer['start_date']),
            'availableEndDateLabel' => Carbon::parse($offer['end_date']),
            'propertyName' => null,
            'disclaimer' => null,
            'rules' => null,
        ];
    }
}