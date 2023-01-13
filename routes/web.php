<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Event\EditController;
use App\Http\Controllers\Event\ShowController;
use App\Http\Controllers\Event\IndexController;
use App\Http\Controllers\Event\StoreController;
use App\Http\Controllers\Event\CreateController;
use App\Http\Controllers\Event\DetachController;
use App\Http\Controllers\Event\UpdateController;
use App\Http\Controllers\Event\DestroyController;

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

Route::get('/users', [UserController::class, 'index'])->middleware(['auth', 'verified'])->name('users');
Route::get('/users/search', [UserController::class, 'search'])->middleware(['auth', 'verified'])->name('users.search');
Route::get('/users/paginate/{event}', [UserController::class, 'paginate'])->middleware(['auth', 'verified'])->name('users.paginate');
Route::get('/users/refresh/event/{event}', [UserController::class, 'refresh'])->middleware(['auth', 'verified'])->name('users.event_refresh');
Route::post('/user/mark-as-read', [UserController::class, 'markNotification'])->name('markNotification');

Route::get('/dashboard', function () {
    return view('dashboard', ['notifications' => auth()->user()->unreadNotifications]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/photo', [ProfileController::class, 'upload'])->name('profile.upload');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/events', IndexController::class)->name('events.index');
    Route::get('/events/create', CreateController::class)->name('events.create');
    Route::post('/events', StoreController::class)->name('events.store');
    Route::get('/events/{event}', ShowController::class)->name('events.show');
    Route::get('/events/{event}/edit', EditController::class)->name('events.edit');
    Route::put('/events/{event}', UpdateController::class)->name('events.update');
    Route::patch('/events/{event}', DetachController::class)->name('events.detach');
    Route::delete('/events/{event}', DestroyController::class)->name('events.destroy');
});

require __DIR__.'/auth.php';
