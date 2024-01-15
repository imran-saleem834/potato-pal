<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Unload;
use App\Models\Receival;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Helpers\CategoriesHelper;
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
        $receivals = Receival::query()
            ->with(['grower' => fn($query) => $query->select('id', 'name', 'grower_name')])
            ->select('id', 'grower_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->search($search);
                });
            })
            ->whereNotNull('status')
            ->latest()
            ->get();

        $receivalId = $request->input('receivalId', $receivals->first()->id ?? 0);

        return Inertia::render('Unload/Index', [
            'receivals'  => $receivals,
            'single'     => $this->getReceivalUnloads($receivalId),
            'categories' => Category::whereIn('type', ['fungicide', 'seed-type'])->get(),
            'filters'    => $request->only(['search']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receival = $this->getReceivalUnloads($id);

        return response()->json($receival);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnloadRequest $request, string $id)
    {
        $receival = Receival::find($id);
        $receival->update($request->only('status'));
        $receival->save();

        $inputs = $request->only(['fungicide']);
        CategoriesHelper::createRelationOfTypes($inputs, $receival->id, Receival::class);

        foreach ($request->input('unloads') as $unloadInputs) {
            $unloadInputs['receival_id'] = $id;
            $unload                      = Unload::updateOrCreate(['id' => $unloadInputs['id'] ?? null], $unloadInputs);

            CategoriesHelper::createRelationOfTypes(
                ['seed_type' => [$unloadInputs['seed_type']]],
                $unload->id,
                Unload::class
            );
        }

        ReceivalHelper::updateUniqueKey($receival);
        
        ReceivalHelper::calculateRemainingReceivals($receival->grower_id);

        NotificationHelper::updatedAction('Unload', $id);

        return to_route('unloading.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Unload::where('receival_id', $id)->delete();

        $receival = Receival::find($id);

        ReceivalHelper::calculateRemainingReceivals($receival->grower_id);

        NotificationHelper::deleteAction('Unload', $id);

        return to_route('unloading.index');
    }

    private function getReceivalUnloads($receivalId)
    {
        return Receival::with([
            'unloads.categories.category',
            'grower'    => fn($query) => $query->select('id', 'name', 'grower_name'),
            'tiaSample' => fn($query) => $query->select(['id', 'status', 'receival_id']),
            'categories.category'
        ])->find($receivalId);
    }
}
