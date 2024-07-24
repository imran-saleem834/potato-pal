<?php

use Dcblogdev\Xero\Facades\Xero;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CuttingController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\SizingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\ReceivalController;
use App\Http\Controllers\TiaSampleController;
use App\Http\Controllers\UnloadingController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\BulkBaggingController;
use App\Http\Controllers\WeighbridgeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReallocationController;
use App\Http\Controllers\ChemicalApplicationController;

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

    Route::middleware(['admin'])->group(function () {
        Route::resource('/users', UserController::class)->except(['create', 'edit']);

        Route::resource('/categories', CategoryController::class);

        Route::resource('/labels', LabelController::class);
        Route::resource('/invoices', InvoiceController::class);

        Route::get('/labels/{label}/print/{type}', [LabelController::class, 'label'])->name('labels.print');

        Route::resource('/reports', ReportController::class);
        Route::get('/reports/{report}/result', [ReportController::class, 'result'])->name('reports.result');
    });

    Route::middleware(['receivals'])->group(function () {
        Route::post('/receivals/{id}/push/unload', [ReceivalController::class, 'pushForUnload'])
            ->name('receivals.push.unload');
        Route::post('/receivals/{id}/push/tia-sample', [ReceivalController::class, 'pushForTiaSample'])
            ->name('receivals.push.tia-sample');
        Route::post('/receivals/{id}/duplicate', [ReceivalController::class, 'duplicate'])
            ->name('receivals.duplicate');
        Route::resource('/receivals', ReceivalController::class);
    });

    Route::middleware(['unloading'])->group(function () {
        Route::delete('/unloading/{unloading}/single', [UnloadingController::class, 'destroySingle'])
            ->name('unloading.single.destroy');
        Route::post('/unloading/{id}/push/sizing', [UnloadingController::class, 'pushForSizing'])
            ->name('unloading.push.sizing');
        Route::resource('/unloading', UnloadingController::class);
    });

    Route::resource('/tia-samples', TiaSampleController::class)->middleware('tia-sampling');

    Route::middleware(['allocations'])->group(function () {
        Route::resource('/allocations', AllocationController::class)->except(['create', 'edit', 'show']);
        Route::get('/growers/{id}/receivals', [AllocationController::class, 'receivals'])->name('growers.receivals');
        Route::post('/allocations/{id}/duplicate', [AllocationController::class, 'duplicate'])->name('allocation.duplicate');
    });

    Route::middleware(['reallocations'])->group(function () {
        Route::resource('/reallocations', ReallocationController::class)->except(['create', 'edit']);
        Route::get('/buyers/{id}/cuttings', [ReallocationController::class, 'cuttings'])->name('buyers.cuttings');
    });

    Route::middleware(['dispatch'])->group(function () {
        Route::resource('/dispatches', DispatchController::class)->except(['create', 'edit', 'show']);
        Route::post('/returns', [DispatchController::class, 'returns'])->name('returns.store');

        Route::get('/buyers/{id}/d/allocations', [DispatchController::class, 'allocations'])->name('d.buyers.allocations');
    });

    Route::middleware(['cutting'])->group(function () {
        Route::resource('/cuttings', CuttingController::class);
        Route::get('/buyers/{id}/c/allocations', [CuttingController::class, 'allocations'])->name('c.buyers.allocations');
    });

    Route::get('/weighbridges', [WeighbridgeController::class, 'index'])
        ->middleware('weighbridges')
        ->name('weighbridges.index');
    
    Route::resource('/grading', GradingController::class)->middleware('grading');
    Route::resource('/sizing', SizingController::class)->middleware('grading');
    Route::resource('/chemical-application', ChemicalApplicationController::class)->middleware('grading');
    Route::resource('/bulk-bagging', BulkBaggingController::class)->middleware('grading');
    Route::get('/buyers/{id}/allocations', [GradingController::class, 'allocations'])->name('buyers.allocations');
    Route::get('/growers/{id}/unloads', [SizingController::class, 'unloads'])->name('grower.unloads');
    
    Route::resource('/notifications', NotificationController::class)->middleware('notifications');
    Route::resource('/notes', NoteController::class)->middleware('notes');
    Route::resource('/files', FileController::class)->middleware('files');

    Route::get('/media/files', [MediaController::class, 'files'])->name('media.files');
    Route::post('/media/{model}/{id}/attach', [MediaController::class, 'attach'])->name('media.attach');
    Route::post('/media/{model}/{id}/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::post('/media/{model}/{id}/delete', [MediaController::class, 'delete'])->name('media.delete');

    Route::inertia('/test', 'Test');
    Route::inertia('/xsl', 'XSL');
    Route::post('/create-table', [\App\Http\Controllers\TableController::class, 'store'])->name('create.table');
});

Route::get('/abc', function () {
    //    $items  = Xero::contacts();

    $params = http_build_query([
        'page'  => null,
        'where' => null,
    ]);

    $result = Xero::get('items?'.$params);
    //    $result = Xero::get('items/4fb5eaa4-99ef-416b-95ed-a09ec76b0ef8');

    echo '<pre>';
    //    print_r($items);

    print_r($result);

    // $result['body']['Contacts'];

    //    print_r($result['body']['Items']);
});

Route::get('/abc2', function () {
    $receivals = \App\Models\Receival::with('unloads.receival.categories.category',
        'unloads.categories.category')->get();
    foreach ($receivals as $receival) {
        \App\Helpers\ReceivalHelper::updateUniqueKey($receival);
    }
    foreach ($receivals->keyBy('grower_id') as $receival) {
        \App\Helpers\ReceivalHelper::calculateRemainingReceivals($receival->grower_id);
    }
});

Route::get('/abc3', function () {
    
});

Route::get('xero', function () {
    if (! Xero::isConnected()) {
        return redirect('xero/connect');
    } else {
        return Xero::getTenantName();
    }
});

Route::get('xero/connect', function () {
    return Xero::connect();
});
