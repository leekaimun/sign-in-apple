<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignInAppleController;
use App\Http\Controllers\AppleVerifyController;

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

Route::view('/', 'welcome');
Route::post('applesigninX', [SignInAppleController::class, 'handleSIWALogin']);
Route::post('applesignin', [AppleVerifyController::class, 'appleVerify']);
Route::get('redirectintent', [SignInAppleController::class, 'redirectIntent']);
