<?php

use App\Http\Controllers\FaceitController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [FaceitController::class, 'index']);

Route::get('auth/faceit', [FaceitController::class, 'redirectToProvider'])->name('faceit.login');
Route::get('auth/faceit/callback', [FaceitController::class, 'handleProviderCallback'])->name('faceit.callback');
