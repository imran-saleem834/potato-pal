<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cutting;
use App\Models\Category;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Helpers\AllocationHelper;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\DeleteRecordsHelper;
use App\Http\Requests\CuttingRequest;
use Illuminate\Database\Eloquent\Builder;

class CuttingController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cuttingBuyers = BuyerHelper::getListOfModelBuyers(Cutting::class);

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
            'filters'       => $request->only(['search']),
        ]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = AllocationHelper::getAvailableAllocationForCutting(
            ['buyer_id' => $id],
            ['categories.category', 'grower:id,grower_name']
        );

        return response()->json($allocations->toArray());
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
            'foreignable_type' => Allocation::class,
            'foreignable_id'   => $inputs['id'],
            'bin_size'         => $inputs['item']['bin_size'],
            'no_of_bins'       => $inputs['no_of_bins'],
            'half_tonnes'      => $request->validated('half_tonnes', 0),
            'one_tonnes'       => $request->validated('one_tonnes', 0),
            'two_tonnes'       => $request->validated('two_tonnes', 0),
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
                'foreignable_type' => Allocation::class,
                'foreignable_id'   => $inputs['id'],
                'is_returned'      => 0,
            ],
            [
                'bin_size'    => $inputs['item']['bin_size'],
                'no_of_bins'  => $inputs['no_of_bins'],
                'half_tonnes' => $request->validated('half_tonnes', 0),
                'one_tonnes'  => $request->validated('one_tonnes', 0),
                'two_tonnes'  => $request->validated('two_tonnes', 0),
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
        CategoriesHelper::deleteCategoryRelations($id, Cutting::class);

        $cutting = Cutting::with(['reallocationItems.allocatable.dispatchItems', 'dispatchItems'])->find($id);
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
                'returnItems',
                'categories.category',
                'item.foreignable.grower:id,grower_name',
                'item.foreignable.categories.category',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('cut_date', 'LIKE', "%{$search}%")
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('categories.category', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable', function (Builder $query) use ($search) {
                            return $query->where('paddock', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable.categories.category', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }
}
