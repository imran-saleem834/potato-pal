<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Label;
use App\Models\Cutting;
use App\Models\Receival;
use App\Models\Allocation;
use App\Models\SizingItem;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\LabelRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
                return $query->search($search);
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
                'item',
                'buyer:id,buyer_name',
                'grower:id,grower_name',
            ])
            ->get();
    }

    private function getCuttings()
    {
        return Cutting::query()
            ->with([
                'buyer:id,buyer_name',
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class => [
                            'categories.category',
                            'grower:id,grower_name',
                            'buyer:id,buyer_name',
                        ],
                        SizingItem::class => [
                            'categories.category',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                            'allocatable.sizeable.buyer:id,buyer_name',
                        ],
                    ]);
                },
            ])
            ->get();
    }

    private function getReallocations()
    {
        return Reallocation::query()
            ->with([
                'buyer:id,buyer_name',
                'item.foreignable.item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class => [
                            'item',
                            'categories.category',
                            'grower:id,grower_name',
                            'buyer:id,buyer_name'
                        ],
                        SizingItem::class => [
                            'categories.category',
                            'allocatable.sizeable.item',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.buyer:id,buyer_name',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                    ]);
                },
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
                Allocation::class        => [
                    'item',
                    'categories.category',
                    'buyer:id,buyer_name',
                ],
                Reallocation::class      => [
                    'item.foreignable.item.foreignable.categories.category', 
                    'item.foreignable.item.foreignable.buyer:id,buyer_name'
                ],
                Cutting::class           => ['item.foreignable.categories.category'],
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
