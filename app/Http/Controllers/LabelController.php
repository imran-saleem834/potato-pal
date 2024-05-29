<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Label;
use App\Models\Receival;
use App\Models\Allocation;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Models\CuttingAllocation;
use App\Helpers\NotificationHelper;
use App\Http\Requests\LabelRequest;
use Illuminate\Database\Eloquent\Builder;

class LabelController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $labels = Label::query()
            ->with(['grower:id,grower_name'])
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->search($search);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $labelId = $request->input('labelId', $labels->items()[0]->id ?? 0);

        return Inertia::render('Label/Index', [
            'labels'        => $labels,
            'single'        => $this->getLabel($labelId),
            'allocations'   => $this->getAllocations(),
            'reallocations' => $this->getReallocations(),
            'cuttings'      => $this->getCuttings(),
            'receivals'     => $this->geReceivals(),
            'filters'       => $request->only(['search']),
        ]);
    }

    private function getAllocations()
    {
        return Allocation::query()
            ->with([
                'buyer:id,buyer_name',
                'grower:id,grower_name',
            ])
            ->get();
    }

    private function getReallocations()
    {
        return Reallocation::query()
            ->with([
                'buyer'             => fn ($query) => $query->select(['id', 'buyer_name']),
                'allocation.item',
                'allocation.buyer:id,buyer_name',
                'allocation.grower:id,grower_name',
            ])
            ->get();
    }

    private function getCuttings()
    {
        return CuttingAllocation::query()
            ->with([
                'allocation.item',
                'allocation.buyer:id,buyer_name',
                'allocation.grower:id,grower_name',
            ])
            ->get();
    }

    private function geReceivals()
    {
        return Receival::query()
            ->select(['id', 'grower_id'])
            ->with(['grower:id,grower_name'])
            ->get()
            ->map(function ($receival) {
                return [
                    'value' => $receival->id,
                    'label' => "{$receival->id} - Grower: {$receival->grower->grower_name}",
                ];
            });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $label = $this->getLabel($id);

        return response()->json($label);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabelRequest $request)
    {
        $label = Label::create($request->validated());

        NotificationHelper::addedAction('Label', $label->id);

        return to_route('labels.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabelRequest $request, string $id)
    {
        $label = Label::find($id);
        $label->update($request->validated());
        $label->save();

        NotificationHelper::updatedAction('Label', $id);

        return to_route('labels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Label::destroy($id);

        NotificationHelper::deleteAction('Label', $id);

        return to_route('labels.index');
    }

    private function getLabel($labelId)
    {
        return Label::with([
            'labelable' => fn ($query) => $query->morphWith([
                Allocation::class        => ['categories.category'],
                Reallocation::class      => ['allocation.categories.category', 'allocation.buyer:id,buyer_name'],
                CuttingAllocation::class => ['allocation.categories.category'],
            ]),
            'receival:id,driver_name,created_at',
            'receival.categories.category',
            'grower:id,grower_name',
            'buyer:id,buyer_name',
        ])->find($labelId);
    }

    public function label($id, $type)
    {
        return Inertia::render('Label/Labels', [
            'type'  => $type,
            'label' => $this->getLabel($id),
        ]);
    }
}
