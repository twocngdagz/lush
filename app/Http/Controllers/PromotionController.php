<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function index(): Response
    {
        $accountId = auth()->user()->account->id;
        $promotions = Promotion::forAccount($accountId)
            ->with(['image', 'type', 'groups', 'ranks', 'properties'])
            ->orderBy('index')
            ->withCount('restrictions')
            ->get();
        return Inertia::render('Promotions/Index', [
            'promotions' => $promotions,
            'user' => auth()->user(),
        ]);
    }
}
