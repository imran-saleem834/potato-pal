<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Sizing;
use App\Models\Cutting;
use App\Models\Category;
use App\Models\Allocation;
use App\Models\SizingItem;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Helpers\AllocationHelper;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\DeleteRecordsHelper;
use App\Http\Requests\CuttingRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CuttingController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cuttingBuyers = BuyerHelper::getListOfModelBuyers(Cutting::class, $request->input('buyer'));

        $firstBuyerId = $cuttingBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $cuttings = $this->getCuttings($inputBuyerId, $request->input('search'));
        if ($cuttings->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $cuttings = $this->getCuttings($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('Cutting/Index', [
            'cuttingBuyers' => $cuttingBuyers,
            'single'        => $cuttings,
            'allocations'   => [],
            'categories'    => fn () => Category::whereIn('type', Cutting::CATEGORY_TYPES)->get(),
            'buyers'        => fn () => BuyerHelper::getAvailableBuyers(),
            'filters'       => $request->only(['search', 'buyer']),
        ]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = AllocationHelper::getAvailableAllocations(
            ['buyer_id' => $id],
            ['categories.category', 'grower:id,grower_name']
        );

        return response()->json($allocations->toArray());
    }

    public function sizing(Request $request, $id)
    {
        $sizings = AllocationHelper::getAvailableSizing(
            ['user_id' => $id],
            [
                'categories.category',
                'allocatable.sizeable.categories.category',
                'allocatable.sizeable.grower:id,grower_name',
            ]
        );

        return response()->json($sizings->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CuttingRequest $request)
    {
        $cutting = Cutting::create($request->validated());

        $inputs = $request->validated('selected_allocation');

        AllocationItem::create([
            'allocatable_type' => Cutting::class,
            'allocatable_id'   => $cutting->id,
            'foreignable_type' => $this->getForeignableType($cutting->type),
            'foreignable_id'   => $inputs['id'],
            'from_half_tonnes' => $request->validated('from_half_tonnes') ?? 0,
            'from_one_tonnes'  => $request->validated('from_one_tonnes') ?? 0,
            'from_two_tonnes'  => $request->validated('from_two_tonnes') ?? 0,
            'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
            'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
            'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
        ]);

        $inputs = $request->only(Cutting::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $cutting->id, Cutting::class);

        NotificationHelper::addedAction('Cutting', $cutting->id);

        return to_route('cuttings.index', ['buyerId' => $cutting->buyer_id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CuttingRequest $request, string $id)
    {
        $cutting = Cutting::find($id);
        $cutting->update($request->validated());
        $cutting->save();

        $inputs = $request->validated('selected_allocation');
        $item   = AllocationItem::updateOrCreate(
            [
                'allocatable_type' => Cutting::class,
                'allocatable_id'   => $cutting->id,
                'foreignable_type' => $this->getForeignableType($cutting->type),
                'foreignable_id'   => $inputs['id'],
                'returned_id'      => null,
            ],
            [
                'from_half_tonnes' => $request->validated('from_half_tonnes') ?? 0,
                'from_one_tonnes'  => $request->validated('from_one_tonnes') ?? 0,
                'from_two_tonnes'  => $request->validated('from_two_tonnes') ?? 0,
                'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
                'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
                'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
            ],
        );

        // Delete those who where not comes to update or create
        AllocationItem::query()
            ->where('allocatable_type', Cutting::class)
            ->where('allocatable_id', $cutting->id)
            ->whereNotIn('id', [$item->id])
            ->delete();

        $inputs = $request->only(Cutting::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $cutting->id, Cutting::class);

        NotificationHelper::updatedAction('Cutting', $id);

        return to_route('cuttings.index', ['buyerId' => $cutting->buyer_id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cutting = Cutting::find($id);
        $buyerId = $cutting->buyer_id;

        DeleteRecordsHelper::deleteCutting($cutting);

        NotificationHelper::deleteAction('Cutting', $id);

        $isCuttingExists = Cutting::where('buyer_id', $buyerId)->exists();

        if ($isCuttingExists) {
            return to_route('cuttings.index', ['buyerId' => $buyerId]);
        }

        return to_route('cuttings.index');
    }

    private function getCuttings($buyerId, $search = '')
    {
        return Cutting::query()
            ->with([
                'categories.category',
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class   => [
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                        SizingItem::class   => [
                            'categories.category',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                    ]);
                },
                'returnItems.returns',
            ])
            ->when($search, function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery
                        ->where('cut_date', 'LIKE', "%{$search}%")
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%")
                        ->orWhereHas('item', function (Builder $query) use ($search) {
                            return $query
                                ->where('foreignable_type', Allocation::class)
                                ->whereHasMorph('foreignable', [Allocation::class], function (Builder $query) use ($search) {
                                    return $query->where('paddock', 'LIKE', "%{$search}%")
                                        ->orWhereRelation('grower', 'grower_name', 'LIKE', "%{$search}%")
                                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                                });
                        })
                        ->orWhereHas('item', function (Builder $query) use ($search) {
                            return $query
                                ->where('foreignable_type', SizingItem::class)
                                ->whereHasMorph('foreignable', [SizingItem::class], function (Builder $query) use ($search) {
                                    return $query->where(function (Builder $query) use ($search) {
                                        return $query->whereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                                    })->orWhere(function (Builder $query) use ($search) {
                                        return $query->where('allocatable_type', Sizing::class)
                                            ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($search) {
                                                return $query
                                                    ->whereHasMorph('sizeable', [Allocation::class], function (Builder $query) use ($search) {
                                                        return $query->where('paddock', 'LIKE', "%{$search}%")
                                                            ->orWhereRelation('grower', 'grower_name', 'LIKE', "%{$search}%")
                                                            ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                                                    });
                                            });
                                    });
                                });
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
        return $type === 'sizing' ? SizingItem::class : Allocation::class;
    }
}
