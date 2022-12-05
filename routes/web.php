<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [HomeController::class, 'home'])
->name('dashboard')
->middleware('auth');
Route::get('/login',[AuthenticatedSessionController::class, 'create'])
->name('login')
->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
->name('login.store')
->middleware('guest');
Route::get('/promotions', [PromotionController::class, 'index'])
    ->name('promotion.index')
    ->middleware('auth');

