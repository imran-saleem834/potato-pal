<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\{Unload, Receival};
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
        $unloads = Unload::query()
            ->with([
                'receival'        => function ($query) {
                    return $query->select('id', 'grower_id');
                },
                'receival.grower' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->select('id', 'receival_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('receival_id', 'LIKE', "%$search%")
                    ->orWhere('status', 'LIKE', "%$search%")
                    ->orWhere('total_seed_bins', 'LIKE', "%$search%")
                    ->orWhere('weight_seed_bins', 'LIKE', "%$search%")
                    ->orWhere('total_oversize_bins', 'LIKE', "%$search%")
                    ->orWhere('weight_oversize_bins', 'LIKE', "%$search%");
            })
            ->latest()
            ->get();

        $unloadId = $request->input('unloadId', $unloads->first()->id ?? 0);

        $unload = Unload::with(['receival.grower', 'receival.tiaSample', 'receival.categories.category'])->find($unloadId);

        $receivals = Receival::query()
            ->with([
                'grower' => function ($query) {
                    return $query->select(['id', 'name']);
                }
            ])
            ->select(['id', 'grower_id'])
            ->get()
            ->map(function ($receival) {
                return ['value' => $receival->id, 'label' => "ReceivalId:{$receival->id} {$receival->grower->name}"];
            });

        return Inertia::render('Unload/Index', [
            'unloads'   => $unloads,
            'single'    => $unload,
            'receivals' => $receivals,
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnloadRequest $request)
    {
        $unload = Unload::create($request->validated());

        NotificationHelper::addedAction('Unload', $unload->id);

        return to_route('unloading.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unload = Unload::with(['receival.grower', 'receival.tiaSample', 'receival.categories.category'])->find($id);

        return response()->json($unload);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnloadRequest $request, string $id)
    {
        $unload = Unload::find($id);
        $unload->update($request->validated());
        $unload->save();

        NotificationHelper::updatedAction('Unload', $id);

        return to_route('unloading.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Unload::destroy($id);

        NotificationHelper::deleteAction('Unload', $id);

        return to_route('unloading.index');
    }
}
