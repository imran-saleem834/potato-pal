<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReceivalController;

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
    Route::resource('/receivals', ReceivalController::class);
});

Route::get('/abc', function () {

});
