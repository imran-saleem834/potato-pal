<?php

namespace App\Http\Controllers;

use App\Helpers\DeleteRecordsHelper;
use App\Models\Dispatch;
use Inertia\Inertia;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Helpers\AllocationHelper;
use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ReallocationRequest;

class ReallocationController extends Controller
{
    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reallocationBuyers = BuyerHelper::getListOfModelBuyers(Reallocation::class);

        $firstBuyerId = $reallocationBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $reallocations = $this->getReallocations($inputBuyerId, $request->input('search'));
        if ($reallocations->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $reallocations = $this->getReallocations($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('Reallocation/Index', [
            'reallocationBuyers' => $reallocationBuyers,
            'single'             => $reallocations,
            'buyers'             => fn() => BuyerHelper::getAvailableBuyers(),
            'filters'            => $request->only(['search']),
        ]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = AllocationHelper::getAvailableAllocationForReallocation(
            ['buyer_id' => $id],
            ['categories.category', 'grower:id,grower_name']
        );
        
        return response()->json($allocations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReallocationRequest $request)
    {
        $reallocation = Reallocation::create($request->validated());

        $inputs = $request->validated('selected_allocation');

        AllocationItem::create([
            'allocatable_type' => Reallocation::class,
            'allocatable_id'   => $reallocation->id,
            'foreignable_type' => Allocation::class,
            'foreignable_id'   => $inputs['id'],
            'bin_size'         => $inputs['item']['bin_size'],
            'no_of_bins'       => $inputs['no_of_bins'],
        ]);

        NotificationHelper::addedAction('Reallocation', $reallocation->id);

        return to_route('reallocations.index', ['buyerId' => $reallocation->buyer_id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReallocationRequest $request, string $id)
    {
        $reallocation = Reallocation::find($id);
        $reallocation->update($request->validated());
        $reallocation->save();

        $inputs = $request->validated('selected_allocation');
        $item   = AllocationItem::updateOrCreate(
            [
                'allocatable_type' => Reallocation::class,
                'allocatable_id'   => $reallocation->id,
                'foreignable_type' => Allocation::class,
                'foreignable_id'   => $inputs['id']
            ],
            [
                'bin_size'   => $inputs['item']['bin_size'],
                'no_of_bins' => $inputs['no_of_bins']
            ]
        );

        // Delete those who where not comes to update or create
        AllocationItem::query()
            ->where('allocatable_type', Reallocation::class)
            ->where('allocatable_id', $reallocation->id)
            ->whereNotIn('id', [$item->id])
            ->delete();

        NotificationHelper::updatedAction('Reallocation', $id);

        return to_route('reallocations.index', ['buyerId' => $reallocation->buyer_id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reallocation = Reallocation::find($id);
        $buyerId      = $reallocation->buyer_id;
        
        DeleteRecordsHelper::deleteReallocation($reallocation);
        
        NotificationHelper::deleteAction('Reallocation', $id);

        $isReallocationExists = Reallocation::where('buyer_id', $buyerId)->exists();
        if ($isReallocationExists) {
            return to_route('reallocations.index', ['buyerId' => $buyerId]);
        }

        return to_route('reallocations.index');
    }

    private function getReallocations($buyerId, $search = '')
    {
        return Reallocation::query()
            ->with([
                'returns',
                'allocationBuyer',
                'item.foreignable.grower:id,grower_name',
                'item.foreignable.categories.category',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('allocationBuyer', function (Builder $query) use ($search) {
                            return $query->where('buyer_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable', function (Builder $query) use ($search) {
                            return $query->where('paddock', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable.grower', function (Builder $query) use ($search) {
                            return $query->where('grower_name', 'LIKE', "%{$search}%");
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
