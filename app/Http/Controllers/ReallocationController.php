<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cutting;
use App\Helpers\BuyerHelper;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Helpers\AllocationHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\DeleteRecordsHelper;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ReallocationRequest;

class ReallocationController extends Controller
{
    /**
     * @param  Request  $request
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
            'buyers'             => fn () => BuyerHelper::getAvailableBuyers(),
            'filters'            => $request->only(['search']),
        ]);
    }

    public function cuttings(Request $request, $id)
    {
        $cuttings = AllocationHelper::getAvailableCuttingsForReallocation(
            ['buyer_id' => $id],
            ['item.foreignable.categories.category', 'item.foreignable.grower:id,grower_name']
        );

        return response()->json($cuttings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReallocationRequest $request)
    {
        $reallocation = Reallocation::create($request->validated());

        $inputs = $request->validated('selected_cutting');

        AllocationItem::create([
            'allocatable_type' => Reallocation::class,
            'allocatable_id'   => $reallocation->id,
            'foreignable_type' => Cutting::class,
            'foreignable_id'   => $inputs['id'],
            'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
            'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
            'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
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

        $inputs = $request->validated('selected_cutting');
        $item   = AllocationItem::updateOrCreate(
            [
                'allocatable_type' => Reallocation::class,
                'allocatable_id'   => $reallocation->id,
                'foreignable_type' => Cutting::class,
                'foreignable_id'   => $inputs['id'],
                'returned_id'      => null,
            ],
            [
                'half_tonnes'      => $request->validated('half_tonnes') ?? 0,
                'one_tonnes'       => $request->validated('one_tonnes') ?? 0,
                'two_tonnes'       => $request->validated('two_tonnes') ?? 0,
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
        $reallocation = Reallocation::with(['dispatchItems'])->find($id);
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
                'returnItems.returns',
                'allocationBuyer',
                'item.foreignable.item.foreignable.grower:id,grower_name',
                'item.foreignable.item.foreignable.categories.category',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('allocationBuyer', function (Builder $query) use ($search) {
                            return $query->where('buyer_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable.item.foreignable', function (Builder $query) use ($search) {
                            return $query->where('paddock', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable.item.foreignable.grower', function (Builder $query) use ($search) {
                            return $query->where('grower_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('item.foreignable.item.foreignable.categories.category', function (Builder $query) use ($search) {
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
