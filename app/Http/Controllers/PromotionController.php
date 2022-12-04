<?php

namespace App\Http\Controllers;

class PromotionController extends Controller
{
    public function index()
    {
        $accountId = auth()->user()->account->id;
        $promotions = Promotion::forAccount($accountId)
            ->with(['image', 'type', 'groups', 'ranks', 'properties'])
            ->orderBy('index')
            ->withCount('restrictions')
            ->get();
    }
}
