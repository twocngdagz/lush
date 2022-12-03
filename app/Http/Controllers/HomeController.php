<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HomeController extends Controller
{
    public function home(): \Inertia\Response
    {
        return Inertia::render('Test', [
            'user' => \App\Models\User::first()
        ]);
    }
}
