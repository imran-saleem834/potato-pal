<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cutting;
use App\Models\Dispatch;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Helpers\AllocationHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\ReturnRequest;
use App\Http\Requests\DispatchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DispatchController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dispatchBuyers = BuyerHelper::getListOfModelBuyers(Dispatch::class);

        $firstBuyerId = $dispatchBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $dispatchs = $this->getDispatchs($inputBuyerId, $request->input('search'));
        if ($dispatchs->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $dispatchs = $this->getDispatchs($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('Dispatch/Index', [
            'dispatchBuyers' => $dispatchBuyers,
            'single'         => $dispatchs,
            'buyers'         => fn () => BuyerHelper::getAvailableBuyers(),
            'filters'        => $request->only(['search']),
        ]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = AllocationHelper::getAvailableAllocationForDispatch(
            ['buyer_id' => $id],
            ['categories.category', 'grower:id,grower_name']
        );

        return response()->json($allocations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DispatchRequest $request)
    {
        $dispatch = Dispatch::create($request->validated());

        $inputs = $request->validated('selected_allocation', []);

        AllocationItem::create([
            'allocatable_type' => Dispatch::class,
            'allocatable_id'   => $dispatch->id,
            'foreignable_type' => $this->getForeignableType($dispatch->type),
            'foreignable_id'   => $inputs['id'],
            'half_tonnes'      => $request->validated('half_tonnes', 0),
            'one_tonnes'       => $request->validated('one_tonnes', 0),
            'two_tonnes'       => $request->validated('two_tonnes', 0),
        ]);

        NotificationHelper::addedAction('Dispatch', $dispatch->id);

        return to_route('dispatches.index', ['buyerId' => $dispatch->buyer_id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DispatchRequest $request, string $id)
    {
        $dispatch = Dispatch::find($id);
        $dispatch->update($request->validated());
        $dispatch->save();

        $inputs = $request->validated('selected_allocation', []);

        AllocationItem::updateOrCreate(
            [
                'allocatable_type' => Dispatch::class,
                'allocatable_id'   => $dispatch->id,
                'foreignable_type' => $this->getForeignableType($dispatch->type),
                'foreignable_id'   => $inputs['id'],
                'is_returned'      => 0,
            ],
            [
                'half_tonnes'      => $request->validated('half_tonnes', 0),
                'one_tonnes'       => $request->validated('one_tonnes', 0),
                'two_tonnes'       => $request->validated('two_tonnes', 0),
            ]
        );

        NotificationHelper::updatedAction('Dispatch', $id);

        return to_route('dispatches.index', ['buyerId' => $dispatch->buyer_id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dispatch = Dispatch::find($id);
        $buyerId  = $dispatch->buyer_id;
        $dispatch->returnItems()->delete();
        $dispatch->item()->delete();
        $dispatch->delete();

        NotificationHelper::deleteAction('Dispatch', $id);

        $isDispatchExists = Dispatch::where('buyer_id', $buyerId)->exists();
        if ($isDispatchExists) {
            return to_route('dispatches.index', ['buyerId' => $buyerId]);
        }

        return to_route('dispatches.index');
    }

    public function returns(ReturnRequest $request)
    {
        $inputs = $request->validated('dispatch');

        AllocationItem::create([
            'allocatable_type' => Dispatch::class,
            'allocatable_id'   => $inputs['id'],
            'foreignable_type' => $this->getForeignableType($inputs['type']),
            'foreignable_id'   => $inputs['item']['foreignable']['id'],
            'is_returned'      => 1,
            'half_tonnes'      => $request->validated('half_tonnes', 0),
            'one_tonnes'       => $request->validated('one_tonnes', 0),
            'two_tonnes'       => $request->validated('two_tonnes', 0),
        ]);

        NotificationHelper::addedAction('Return', $inputs['id']);

        return to_route('dispatches.index', ['buyerId' => $inputs['buyer_id']]);
    }

    private function getDispatchs($buyerId, $search = '')
    {
        return Dispatch::query()
            ->with([
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Reallocation::class => [
                            'item.foreignable.item.foreignable.categories.category',
                            'item.foreignable.item.foreignable.grower:id,grower_name',
                        ],
                        Cutting::class => [
                            'item.foreignable.categories.category',
                            'item.foreignable.grower:id,grower_name',
                        ],
                        Allocation::class   => [
                            'categories.category', 
                            'grower:id,grower_name'
                        ],
                    ]);
                },
                'allocationBuyer',
                'returnItems',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('comment', 'LIKE', "%{$search}%")
                        ->orWhereHasMorph(
                            'item.foreignable',
                            [Allocation::class, Cutting::class, Reallocation::class],
                            function (Builder $query, string $type) use ($search) {
                                if ($type === Allocation::class) {
                                    return $query->where('paddock', 'LIKE', "%{$search}%")
                                        ->orWhereRelation('buyer', 'buyer_name', 'LIKE', "%{$search}%")
                                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                                } else if ($type === Cutting::class) {
                                    return $query->whereRelation('item.foreignable', 'paddock', 'LIKE', "%{$search}%")
                                        ->orWhereRelation('item.foreignable.buyer', 'buyer_name', 'LIKE', "%{$search}%")
                                        ->orWhereRelation('item.foreignable.categories.category', 'name', 'LIKE', "%{$search}%");
                                }

                                return $query->whereRelation('item.foreignable.item.foreignable', 'paddock', 'LIKE', "%{$search}%")
                                    ->orWhereRelation('item.foreignable.item.foreignable.buyer', 'buyer_name', 'LIKE', "%{$search}%")
                                    ->orWhereRelation('item.foreignable.item.foreignable.categories.category', 'name', 'LIKE', "%{$search}%");
                            }
                        );
                });
            })
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }

    private function getForeignableType($type)
    {
        if ($type === 'reallocation') {
            return Reallocation::class;
        } else if ($type === 'cutting') {
            return Cutting::class;
        }
        return Allocation::class;
    }
}
