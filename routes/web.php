<?php

use Dcblogdev\Xero\Facades\Xero;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SizingController;
use App\Http\Controllers\CuttingController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\ReturnsController;
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
    Route::post('/role/{role}', [UserController::class, 'changeRole'])->name('change-role');

    Route::middleware(['admin'])->group(function () {
        Route::resource('/users', UserController::class)->except(['create', 'edit']);

        Route::resource('/categories', CategoryController::class);

        Route::resource('/labels', LabelController::class);
        Route::resource('/invoices', InvoiceController::class);

        Route::get('/labels/{label}/print/{type}', [LabelController::class, 'label'])->name('labels.print');
    });

    Route::resource('/reports', ReportController::class);
    Route::get('/reports/{report}/result', [ReportController::class, 'result'])->name('reports.result');

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
        Route::resource('/returns', ReturnsController::class)->only(['store', 'update', 'destroy']);

        Route::group(['prefix' => 'buyers/{id}/dispatch', 'as' => 'dispatch.buyers.'], function () {
            Route::get('/allocations', [DispatchController::class, 'allocations'])->name('allocation');
            Route::get('/reallocations', [DispatchController::class, 'reallocations'])->name('reallocation');
            Route::get('/cuttings', [DispatchController::class, 'cuttings'])->name('cutting');
            Route::get('/sizings', [DispatchController::class, 'sizings'])->name('sizing');
        });
    });

    Route::middleware(['cutting'])->group(function () {
        Route::resource('/cuttings', CuttingController::class);

        Route::group(['prefix' => 'buyers/{id}/cutting', 'as' => 'cutting.buyers.'], function () {
            Route::get('/allocations', [CuttingController::class, 'allocations'])->name('allocation');
            Route::get('/sizing', [CuttingController::class, 'sizing'])->name('sizing');
        });
    });

    Route::get('/weighbridges', [WeighbridgeController::class, 'index'])
        ->middleware('weighbridges')
        ->name('weighbridges.index');

    Route::group(['middleware' => 'grading'], function () {
        Route::resource('/grading', GradingController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/grading/buyers/{id}/allocations', [GradingController::class, 'allocations'])->name('grading.buyers.allocations');
        Route::get('/grading/growers/{id}/unloads', [GradingController::class, 'unloads'])->name('grading.grower.unloads');
    });

    Route::group(['middleware' => 'sizing'], function () {
        Route::resource('/sizing', SizingController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/sizing/buyers/{id}/allocations', [SizingController::class, 'allocations'])->name('sizing.buyers.allocations');
        Route::get('/sizing/growers/{id}/unloads', [SizingController::class, 'unloads'])->name('sizing.grower.unloads');
    });
    
    Route::resource('/chemical-application', ChemicalApplicationController::class)
        ->middleware('chemical-application');
    Route::resource('/bulk-bagging', BulkBaggingController::class)
        ->middleware('bulk-bagging');
    Route::get('/buyers/{id}/allocations', [GradingController::class, 'allocations'])->name('buyers.allocations');

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
    $users = \App\Models\User::all();
    foreach ($users as $user) {
        $user->role = in_array('admin', $user->access) ? 'admin' : ($user->access[0] ?? null);
        $user->save();
    }
});

Route::get('/download-receivals', function () {
    return Maatwebsite\Excel\Facades\Excel::download(new App\Exports\ReceivalsExport, 'receivals.xlsx');
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
