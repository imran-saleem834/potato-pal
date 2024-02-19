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
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\CuttingController;
use App\Http\Controllers\ReallocationController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WeighbridgeController;

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

    Route::resource('/categories', CategoryController::class);

    Route::post('/receivals/{id}/push/unload',
        [ReceivalController::class, 'pushForUnload'])->name('receivals.push.unload');
    Route::post('/receivals/{id}/push/tia-sample',
        [ReceivalController::class, 'pushForTiaSample'])->name('receivals.push.tia-sample');
    Route::post('/receivals/{id}/duplicate', [ReceivalController::class, 'duplicate'])->name('receivals.duplicate');
    Route::post('/receivals/{id}/upload', [ReceivalController::class, 'upload'])->name('receivals.upload');
    Route::post('/receivals/{id}/delete', [ReceivalController::class, 'delete'])->name('receivals.delete');
    Route::resource('/receivals', ReceivalController::class);

    Route::resource('/unloading', UnloadingController::class);
    Route::resource('/cuttings', CuttingController::class);

    Route::post('/tia-samples/{id}/upload', [TiaSampleController::class, 'upload'])->name('tia-samples.upload');
    Route::post('/tia-samples/{id}/delete', [TiaSampleController::class, 'delete'])->name('tia-samples.delete');
    Route::resource('/tia-samples', TiaSampleController::class);

    Route::resource('/allocations', AllocationController::class)->except(['create', 'edit', 'show']);
    Route::resource('/reallocations', ReallocationController::class)->except(['create', 'edit']);
    Route::resource('/dispatches', DispatchController::class)->except(['create', 'edit', 'show']);

    Route::get('/weighbridges', [WeighbridgeController::class, 'index'])->name('weighbridges.index');
    
    Route::resource('/reports', ReportController::class);
    Route::get('/reports/{report}/result', [ReportController::class, 'result'])->name('reports.result');

    Route::resource('/notifications', NotificationController::class);

    Route::post('/notes/{id}/upload', [NoteController::class, 'upload'])->name('notes.upload');
    Route::post('/notes/{id}/delete', [NoteController::class, 'delete'])->name('notes.delete');
    Route::resource('/notes', NoteController::class);

    Route::resource('/files', FileController::class);
    Route::inertia('/test', 'Test');
});

Route::get('/abc', function () {
    $a = [
        'grower_group' => [1, 2, 4],
        'buyer_group'  => [1, 2, 4],
        'cool_store'   => [1, 2, 4],
        'fungis'       => [1, 2, 4],
    ];
    echo "<pre>";
    print_r($a);
    $keys = array_map(function ($b) {
        return str_replace('_', '-', $b);
    }, array_keys($a));
    print_r($keys);
    print_r(array_combine($keys, $a));
});
