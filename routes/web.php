<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReceivalController;
use App\Http\Controllers\UnloadingController;

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

Route::inertia('/terms-of-service', 'TermsOfService')->name('terms.show');
Route::inertia('/privacy-policy', 'PrivacyPolicy')->name('policy.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::inertia('/dashboard', 'Dashboard');
    Route::inertia('/', 'Dashboard')->name('dashboard');
    Route::inertia('/users', 'User/Index')->name('users');

    Route::get('/users/list', [UserController::class, 'index'])->name('users.list');
    Route::resource('/users', UserController::class)->except(['index', 'create', 'edit']);

    Route::inertia('/options', 'AdminOption/Index')->name('options');
    Route::resource('/categories', CategoryController::class);

    Route::get('/receivals/list', [ReceivalController::class, 'list'])->name('receivals.list');
    Route::post('/receivals/{id}/push/unload', [ReceivalController::class, 'pushForUnload'])->name('receivals.push.unload');
    Route::post('/receivals/{id}/push/tia-sample', [ReceivalController::class, 'pushForTiaSample'])->name('receivals.push.tia-sample');
    Route::resource('/receivals', ReceivalController::class);

    Route::get('/unloading/list', [UnloadingController::class, 'list'])->name('unloading.list');
    Route::resource('/unloading', UnloadingController::class);
});

Route::get('/abc', function () {

});
