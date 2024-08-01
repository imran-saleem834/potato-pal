<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Sizing;
use App\Models\Cutting;
use App\Models\Category;
use App\Models\Dispatch;
use App\Models\Allocation;
use App\Models\SizingItem;
use App\Helpers\BuyerHelper;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Models\DispatchReturn;
use App\Helpers\AllocationHelper;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\DeleteRecordsHelper;
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
        $dispatchBuyers = BuyerHelper::getListOfModelBuyers(Dispatch::class, $request->input('buyer'));

        $firstBuyerId = $dispatchBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $dispatchs = $this->getDispatchs($inputBuyerId, $request->input('search'));
        if ($dispatchs->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $dispatchs = $this->getDispatchs($firstBuyerId, $request->input('search'));
        }

        $summery = AllocationItem::query()
            ->selectRaw('SUM(`half_tonnes`) AS `sum_half_tonnes`, SUM(`one_tonnes`) AS `sum_one_tonnes`, SUM(`two_tonnes`) AS `sum_two_tonnes`')
            ->where('allocatable_type', Dispatch::class)
            ->whereNull('returned_id')
            ->whereHasMorph('foreignable', [Allocation::class, Reallocation::class, Cutting::class, SizingItem::class],
                function (Builder $query, $type) use ($inputBuyerId) {
                    if ($type !== SizingItem::class) {
                        return $query->where('buyer_id', '=', $inputBuyerId);
                    }

                    return $query->whereHasMorph('allocatable', [Sizing::class],
                        function (Builder $query) use ($inputBuyerId) {
                            return $query->where('user_id', '=', $inputBuyerId);
                        });
                })
            ->first();

        return Inertia::render('Dispatch/Index', [
            'dispatchBuyers' => $dispatchBuyers,
            'summery'        => $summery,
            'single'         => $dispatchs,
            'buyers'         => fn () => $this->getAvailableBuyers(),
            'categories'     => Category::whereIn('type', ['transport'])->get(),
            'filters'        => $request->only(['search', 'buyer']),
        ]);
    }

    private function getAvailableBuyers()
    {
        return User::query()
            ->with(['categories' => fn ($query) => $query->with(['category'])->where('type', 'buyer-group')])
            ->select(['id', 'buyer_name'])
            ->whereJsonContains('role', 'buyer')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->buyer_name, 'categories' => $user->categories]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = AllocationHelper::getAvailableAllocationForDispatch(
            ['buyer_id' => $id],
            ['categories.category', 'grower:id,grower_name']
        );

        return response()->json($allocations);
    }

    public function reallocations(Request $request, $id)
    {
        $reallocations = AllocationHelper::getAvailableReallocationForDispatch(
            ['buyer_id' => $id],
            [
                'item.foreignable.item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class => [
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                        SizingItem::class => [
                            'categories.category',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                    ]);
                },
            ]
        );

        return response()->json($reallocations);
    }

    public function cuttings(Request $request, $id)
    {
        $cuttings = AllocationHelper::getAvailableCuttingsForReallocation(
            ['buyer_id' => $id],
            [
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class => [
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                        SizingItem::class => [
                            'categories.category',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                    ]);
                },
            ]
        );

        return response()->json($cuttings);
    }

    public function sizings(Request $request, $id)
    {
        $sizings = AllocationHelper::getAvailableSizingForDispatch(
            ['user_id' => $id],
            [
                'categories.category',
                'allocatable.sizeable.categories.category',
                'allocatable.sizeable.grower:id,grower_name',
            ]
        );

        return response()->json($sizings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DispatchRequest $request)
    {
        $dispatch = Dispatch::create($request->validated());

        if (! empty($request->validated('created_at'))) {
            $dispatch->created_at = Carbon::parse($request->validated('created_at'));
            $dispatch->save();
        }

        $inputs = $request->only(Dispatch::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $dispatch->id, Dispatch::class);

        $inputs = $request->validated('selected_allocation', []);

        AllocationItem::create([
            'allocatable_type' => Dispatch::class,
            'allocatable_id'   => $dispatch->id,
            'foreignable_type' => $this->getForeignableType($dispatch->dispatch_type),
            'foreignable_id'   => $inputs['id'],
            'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
            'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
            'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
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

        if (! empty($request->validated('created_at'))) {
            $dispatch->created_at = Carbon::parse($request->validated('created_at'));
            $dispatch->save();
        }

        $inputs = $request->only(Dispatch::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $dispatch->id, Dispatch::class);

        $inputs = $request->validated('selected_allocation', []);

        AllocationItem::updateOrCreate(
            [
                'allocatable_type' => Dispatch::class,
                'allocatable_id'   => $dispatch->id,
                'foreignable_type' => $this->getForeignableType($dispatch->dispatch_type),
                'foreignable_id'   => $inputs['id'],
                'returned_id'      => null,
            ],
            [
                'half_tonnes' => $request->validated('half_tonnes') ?? 0,
                'one_tonnes'  => $request->validated('one_tonnes') ?? 0,
                'two_tonnes'  => $request->validated('two_tonnes') ?? 0,
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
        DeleteRecordsHelper::deleteReturnItems($dispatch);
        $dispatch->item()->delete();
        $dispatch->delete();

        NotificationHelper::deleteAction('Dispatch', $id);

        $isDispatchExists = Dispatch::where('buyer_id', $buyerId)->exists();
        if ($isDispatchExists) {
            return to_route('dispatches.index', ['buyerId' => $buyerId]);
        }

        return to_route('dispatches.index');
    }

    private function getDispatchs($buyerId, $search = '')
    {
        return Dispatch::query()
            ->with([
                'categories.category',
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Reallocation::class => [
                            'item.foreignable.item.foreignable' => function (MorphTo $morphTo) {
                                $morphTo->morphWith([
                                    Allocation::class => [
                                        'categories.category',
                                        'grower:id,grower_name',
                                    ],
                                    SizingItem::class => [
                                        'categories.category',
                                        'allocatable.sizeable.categories.category',
                                        'allocatable.sizeable.grower:id,grower_name',
                                    ],
                                ]);
                            },
                        ],
                        Cutting::class      => [
                            'item.foreignable' => function (MorphTo $morphTo) {
                                $morphTo->morphWith([
                                    Allocation::class => [
                                        'categories.category',
                                        'grower:id,grower_name',
                                    ],
                                    SizingItem::class => [
                                        'categories.category',
                                        'allocatable.sizeable.categories.category',
                                        'allocatable.sizeable.grower:id,grower_name',
                                    ],
                                ]);
                            },
                        ],
                        SizingItem::class   => [
                            'categories.category',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                        Allocation::class   => [
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                    ]);
                },
                'returnItems.returns',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%")
                        ->orWhereHas('item', function (Builder $query) use ($search) {
                            return $query
                                ->where('foreignable_type', Allocation::class)
                                ->whereHasMorph('foreignable', [Allocation::class],
                                    function (Builder $query) use ($search) {
                                        return $query->where('paddock', 'LIKE', "%{$search}%")
                                            ->orWhereRelation('buyer', 'buyer_name', 'LIKE', "%{$search}%")
                                            ->orWhereRelation('grower', 'grower_name', 'LIKE', "%{$search}%")
                                            ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                                    }
                                );
                        })
                        ->orWhereHas('item', function (Builder $query) use ($search) {
                            return $query
                                ->where('foreignable_type', SizingItem::class)
                                ->whereHasMorph('foreignable', [SizingItem::class],
                                    function (Builder $query) use ($search) {
                                        return $query->where(function (Builder $query) use ($search) {
                                            return $query->whereRelation('categories.category', 'name', 'LIKE',
                                                "%{$search}%");
                                        })->orWhere(function (Builder $query) use ($search) {
                                            return $query->where('allocatable_type', Sizing::class)
                                                ->whereHasMorph('allocatable', [Sizing::class],
                                                    function (Builder $query) use ($search) {
                                                        return $query
                                                            ->whereHasMorph('sizeable', [Allocation::class],
                                                                function (Builder $query) use ($search) {
                                                                    return $query->where('paddock', 'LIKE',
                                                                        "%{$search}%")
                                                                        ->orWhereRelation('categories.category', 'name',
                                                                            'LIKE', "%{$search}%");
                                                                });
                                                    });
                                        });
                                    });
                        })
                        ->orWhereHas('item', function (Builder $query) use ($search) {
                            return $query
                                ->where('foreignable_type', Cutting::class)
                                ->whereHasMorph('foreignable', [Cutting::class],
                                    function (Builder $query) use ($search) {
                                        return $query->whereHas('item', function (Builder $query) use ($search) {
                                            return $query
                                                ->where('foreignable_type', Allocation::class)
                                                ->whereHasMorph('foreignable', [Allocation::class], function (
                                                    Builder $query
                                                ) use ($search) {
                                                    return $query->where('paddock', 'LIKE', "%{$search}%")
                                                        ->orWhereRelation('grower', 'grower_name', 'LIKE',
                                                            "%{$search}%")
                                                        ->orWhereRelation('categories.category', 'name', 'LIKE',
                                                            "%{$search}%");
                                                });
                                        })->orWhereHas('item', function (Builder $query) use ($search) {
                                            return $query
                                                ->where('foreignable_type', SizingItem::class)
                                                ->whereHasMorph('foreignable', [SizingItem::class], function (
                                                    Builder $query
                                                ) use ($search) {
                                                    return $query->where(function (Builder $query) use ($search) {
                                                        return $query->whereRelation('categories.category', 'name',
                                                            'LIKE', "%{$search}%");
                                                    })->orWhere(function (Builder $query) use ($search) {
                                                        return $query->where('allocatable_type', Sizing::class)
                                                            ->whereHasMorph('allocatable', [Sizing::class],
                                                                function (Builder $query) use ($search) {
                                                                    return $query
                                                                        ->whereHasMorph('sizeable', [Allocation::class],
                                                                            function (Builder $query) use ($search) {
                                                                                return $query->where('paddock', 'LIKE',
                                                                                    "%{$search}%")
                                                                                    ->orWhereRelation('categories.category',
                                                                                        'name', 'LIKE', "%{$search}%");
                                                                            });
                                                                });
                                                    });
                                                });
                                        });
                                    });
                        })
                        ->orWhereHas('item', function (Builder $query) use ($search) {
                            return $query
                                ->where('foreignable_type', Reallocation::class)
                                ->whereHasMorph('foreignable', [Reallocation::class],
                                    function (Builder $query) use ($search) {
                                        return $query->whereHas('item', function (Builder $query) use ($search) {
                                            return $query
                                                ->where('foreignable_type', Cutting::class)
                                                ->whereHasMorph('foreignable', [Cutting::class],
                                                    function (Builder $query) use ($search) {
                                                        return $query->whereHas('item', function (Builder $query) use (
                                                            $search
                                                        ) {
                                                            return $query
                                                                ->where('foreignable_type', Allocation::class)
                                                                ->whereHasMorph('foreignable', [Allocation::class],
                                                                    function (Builder $query) use ($search) {
                                                                        return $query->where('paddock', 'LIKE',
                                                                            "%{$search}%")
                                                                            ->orWhereRelation('grower', 'grower_name',
                                                                                'LIKE', "%{$search}%")
                                                                            ->orWhereRelation('categories.category',
                                                                                'name', 'LIKE', "%{$search}%");
                                                                    });
                                                        })->orWhereHas('item', function (Builder $query) use ($search) {
                                                            return $query
                                                                ->where('foreignable_type', SizingItem::class)
                                                                ->whereHasMorph('foreignable', [SizingItem::class],
                                                                    function (Builder $query) use ($search) {
                                                                        return $query->where(function (Builder $query
                                                                        ) use ($search) {
                                                                            return $query->whereRelation('categories.category',
                                                                                'name', 'LIKE', "%{$search}%");
                                                                        })->orWhere(function (Builder $query) use (
                                                                            $search
                                                                        ) {
                                                                            return $query->where('allocatable_type',
                                                                                Sizing::class)
                                                                                ->whereHasMorph('allocatable',
                                                                                    [Sizing::class],
                                                                                    function (Builder $query) use (
                                                                                        $search
                                                                                    ) {
                                                                                        return $query
                                                                                            ->whereHasMorph('sizeable',
                                                                                                [Allocation::class],
                                                                                                function (Builder $query
                                                                                                ) use ($search) {
                                                                                                    return $query->where('paddock',
                                                                                                        'LIKE',
                                                                                                        "%{$search}%")
                                                                                                        ->orWhereRelation('categories.category',
                                                                                                            'name',
                                                                                                            'LIKE',
                                                                                                            "%{$search}%");
                                                                                                });
                                                                                    });
                                                                        });
                                                                    });
                                                        });
                                                    });
                                        });
                                    }
                                );
                        });
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
        } elseif ($type === 'cutting') {
            return Cutting::class;
        } elseif ($type === 'sizing') {
            return SizingItem::class;
        }

        return Allocation::class;
    }
}
