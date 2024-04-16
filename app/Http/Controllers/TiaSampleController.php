<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Receival;
use App\Models\TiaSample;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\TiaSampleRequest;
use Illuminate\Database\Eloquent\Builder;

class TiaSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tiaSamples = TiaSample::query()
            ->with([
                'receival'        => fn ($query) => $query->select(['id', 'grower_id']),
                'receival.grower' => fn ($query) => $query->select(['id', 'grower_name']),
            ])
            ->select('id', 'receival_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->where('id', 'LIKE', "%$search%")
                        ->orWhere('receival_id', 'LIKE', "%$search%")
                        ->orWhere('processor', 'LIKE', "%$search%")
                        ->orWhere('inspection_no', 'LIKE', "%$search%")
                        ->orWhere('inspection_date', 'LIKE', "%$search%")
                        ->orWhere('size', 'LIKE', "%$search%")
                        ->orWhere('comment', 'LIKE', "%$search%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $tiaSampleId = $request->input('tiaSampleId', $tiaSamples->items()[0]->id ?? 0);

        $tiaSample = $this->getTiaSample($tiaSampleId);

        $receivals = Receival::query()
            ->with(['grower:id,grower_name'])
            ->select(['id', 'grower_id'])
            ->get()
            ->map(function ($receival) {
                return [
                    'value' => $receival->id,
                    'label' => "Receival id: {$receival->id}; {$receival->grower->grower_name}",
                ];
            });

        return Inertia::render('TiaSample/Index', [
            'tiaSamples' => $tiaSamples,
            'single'     => $tiaSample,
            'receivals'  => $receivals,
            'filters'    => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TiaSampleRequest $request)
    {
        $tiaSample = TiaSample::create($request->validated());

        $receivalId = $request->input('receival_id');
        Receival::find($receivalId)->update(['tia_status' => $request->input('status')]);

        NotificationHelper::addedAction('Tia Sample', $tiaSample->id);

        return to_route('tia-samples.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tiaSample = $this->getTiaSample($id);

        return response()->json($tiaSample);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TiaSampleRequest $request, string $id)
    {
        $tiaSample = TiaSample::find($id);
        $tiaSample->update($request->validated());
        $tiaSample->save();

        $receivalId = $tiaSample->getAttribute('receival_id');
        Receival::find($receivalId)->update(['tia_status' => $request->input('status')]);

        NotificationHelper::updatedAction('Tia Sample', $id);

        return to_route('tia-samples.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        TiaSample::destroy($id);

        NotificationHelper::deleteAction('Tia Sample', $id);

        return to_route('tia-samples.index');
    }

    public function getTiaSample(string $id)
    {
        return TiaSample::query()
            ->with([
                'receival.grower.categories.category',
                'receival.categories.category',
            ])
            ->find($id);
    }
}
