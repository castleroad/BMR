<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\EventController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user', function (Request $request) {
    if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])){
        return response()->json(['message' => 'Invalid credentials'], 418);
    }
    
    return [
        'god-token' => Auth::user()->createToken('api-token',['create', 'update', 'delete'])->plainTextToken,
    ];
});


Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('events', EventController::class);
    Route::delete('events/{event}/detach', [EventController::class, 'detach'])->name('events.detach');
});