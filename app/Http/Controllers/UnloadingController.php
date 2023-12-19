<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Receival;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\UnloadRequest;
use Illuminate\Database\Eloquent\Builder;

class UnloadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $unloads = Receival::query()
            ->with(['grower' => fn($query) => $query->select('id', 'name')])
            ->select('id', 'grower_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->search($search);
                });
            })
            ->whereNotNull('status')
            ->latest()
            ->get();

        $unloadId = $request->input('unloadId', $unloads->first()->id ?? 0);

        $unload = Receival::with(['grower', 'tiaSample', 'categories.category'])->find($unloadId);

        return Inertia::render('Unload/Index', [
            'unloads' => $unloads,
            'single'  => $unload,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unload = Receival::with(['grower', 'tiaSample', 'categories.category'])->find($id);

        return response()->json($unload);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnloadRequest $request, string $id)
    {
        $unload = Receival::find($id);
        $unload->update($request->validated());
        $unload->save();

        ReceivalHelper::calculateRemainingReceivals($unload->grower_id);

        NotificationHelper::updatedAction('Unload', $id);

        return to_route('unloading.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unload             = Receival::find($id);
        $unload->status     = null;
        $unload->no_of_bins = null;
        $unload->weight     = null;
        $unload->save();

        ReceivalHelper::calculateRemainingReceivals($unload->grower_id);

        NotificationHelper::deleteAction('Unload', $id);

        return to_route('unloading.index');
    }
}
