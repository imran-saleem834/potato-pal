<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReceivalController;
use App\Http\Controllers\UnloadingController;
use App\Http\Controllers\TiaSampleController;
use App\Http\Controllers\NotificationController;

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

    Route::resource('/users', UserController::class)->except(['create', 'edit']);

    Route::get('/options', [CategoryController::class, 'options'])->name('options');
    Route::resource('/categories', CategoryController::class);

    Route::get('/receivals/list', [ReceivalController::class, 'list'])->name('receivals.list');
    Route::post('/receivals/{id}/push/unload', [ReceivalController::class, 'pushForUnload'])->name('receivals.push.unload');
    Route::post('/receivals/{id}/push/tia-sample', [ReceivalController::class, 'pushForTiaSample'])->name('receivals.push.tia-sample');
    Route::resource('/receivals', ReceivalController::class);

    Route::get('/unloading/list', [UnloadingController::class, 'list'])->name('unloading.list');
    Route::resource('/unloading', UnloadingController::class);

    Route::get('/tia-sample/list', [TiaSampleController::class, 'list'])->name('tia-sample.list');
    Route::post('/tia-sample/{id}/upload', [TiaSampleController::class, 'upload'])->name('tia-sample.upload');
    Route::post('/tia-sample/{id}/delete', [TiaSampleController::class, 'delete'])->name('tia-sample.delete');
    Route::resource('/tia-sample', TiaSampleController::class);

    Route::get('/notifications/list', [NotificationController::class, 'list'])->name('notifications.list');
    Route::resource('/notifications', NotificationController::class);

    Route::get('/notes/list', [NoteController::class, 'list'])->name('notes.list');
    Route::post('/notes/{id}/upload', [NoteController::class, 'upload'])->name('notes.upload');
    Route::post('/notes/{id}/delete', [NoteController::class, 'delete'])->name('notes.delete');
    Route::resource('/notes', NoteController::class);

    Route::get('/files/list', [FileController::class, 'list'])->name('files.list');
    Route::post('/files/{id}/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('/files/{id}/delete', [FileController::class, 'delete'])->name('files.delete');
    Route::resource('/files', FileController::class);
});

Route::get('/abc', function () {

});
