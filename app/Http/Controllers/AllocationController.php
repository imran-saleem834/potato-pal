<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Unload;
use App\Models\Receival;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Models\AllocationItem;
use App\Helpers\ReceivalHelper;
use App\Helpers\CategoriesHelper;
use App\Models\CategoriesRelation;
use App\Helpers\NotificationHelper;
use App\Helpers\DeleteRecordsHelper;
use App\Http\Requests\AllocationRequest;
use Illuminate\Database\Eloquent\Builder;

class AllocationController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allocationBuyers = BuyerHelper::getListOfModelBuyers(Allocation::class, $request->input('buyer'));

        $firstBuyerId = $allocationBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $allocations = $this->getAllocations($inputBuyerId, $request->input('search'));
        if ($allocations->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $allocations = $this->getAllocations($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('Allocation/Index', [
            'allocationBuyers' => $allocationBuyers,
            'single'           => $allocations,
            'growers'          => fn () => BuyerHelper::getAvailableGrowers(),
            'buyers'           => fn () => BuyerHelper::getAvailableBuyers(),
            'filters'          => $request->only(['search', 'buyer']),
        ]);
    }

    public function receivals(Request $request, $id)
    {
        $grower = User::query()
            ->with(['remainingReceivals'])
            ->select(['id', 'grower_name'])
            ->whereJsonContains('role', 'grower')
            ->findOrFail($id);

        $receivals = collect([]);
        foreach ($grower->remainingReceivals as $remainingReceival) {
            if (empty($remainingReceival->receival_id)) {
                continue;
            }
            if (empty($remainingReceival->unload_id)) {
                continue;
            }

            $receivalId = $remainingReceival->receival_id[0];
            $unloadId   = $remainingReceival->unload_id[0];
            $receival   = Receival::select(['id', 'paddocks'])->with(['categories.category'])->find($receivalId);
            $unload     = Unload::select(['id', 'bin_size'])->with(['categories.category'])->find($unloadId);

            $receivals[] = [
                'remaining_receival_id' => $remainingReceival->id,
                'grower_name'           => $grower->grower_name,
                'grower_id'             => $remainingReceival->grower_id,
                'unique_key'            => $remainingReceival->unique_key,
                'bin_size'              => $unload->bin_size,
                'paddock'               => $receival->paddocks[0] ?? '',
                'no_of_bins'            => $remainingReceival->no_of_bins,
                'weight'                => $remainingReceival->weight,
                'receival_categories'   => $receival->categories->toArray(),
                'unload_categories'     => $unload->categories->toArray(),
            ];
        }

        return response()->json($receivals->values()->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllocationRequest $request)
    {
        $allocation = Allocation::create($request->validated());

        $inputs = $request->only(Allocation::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $allocation->id, Allocation::class);

        AllocationItem::create([
            'allocatable_type' => Allocation::class,
            'allocatable_id'   => $allocation->id,
            'bin_size'         => $request->input('bin_size'),
            'no_of_bins'       => $request->input('no_of_bins'),
            'weight'           => $request->input('weight'),
        ]);

        ReceivalHelper::calculateRemainingReceivals($allocation->grower_id);

        NotificationHelper::addedAction('Allocation', $allocation->id);

        return to_route('allocations.index', ['buyerId' => $allocation->buyer_id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AllocationRequest $request, string $id)
    {
        $allocation = Allocation::find($id);
        $buyerId    = $allocation->buyer_id;
        $allocation->update($request->validated());
        $allocation->save();

        $inputs = $request->only(Allocation::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $allocation->id, Allocation::class);

        AllocationItem::updateOrCreate(
            [
                'allocatable_type' => Allocation::class,
                'allocatable_id'   => $allocation->id,
                'returned_id'      => null,
            ],
            $request->safe()->only(['bin_size', 'no_of_bins', 'weight'])
        );

        ReceivalHelper::calculateRemainingReceivals($allocation->grower_id);

        NotificationHelper::updatedAction('Allocation', $id);

        return to_route('allocations.index', ['buyerId' => $buyerId]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $allocation = Allocation::find($id);
        $buyerId    = $allocation->buyer_id;
        $growerId   = $allocation->grower_id;

        DeleteRecordsHelper::deleteAllocation($allocation);

        NotificationHelper::deleteAction('Allocation', $id);

        ReceivalHelper::calculateRemainingReceivals($growerId);

        $isAllocationExists = Allocation::where('buyer_id', $buyerId)->exists();
        if ($isAllocationExists) {
            return to_route('allocations.index', ['buyerId' => $buyerId]);
        }

        return to_route('allocations.index');
    }

    public function duplicate(Request $request, string $id)
    {
        $allocation = Allocation::with(['categories', 'item'])->find($id);

        $request->validate([
            'buyer_id'   => ['required', 'numeric', 'exists:users,id'],
            'no_of_bins' => ['required', 'numeric', 'gt:0', "max:{$allocation->item->no_of_bins}"],
            'weight'     => ['required', 'numeric', 'gt:0', "max:{$allocation->item->weight}"],
        ]);

        $newAllocation = Allocation::create(array_merge(
            $request->only('buyer_id'),
            $allocation->only(['grower_id', 'unique_key', 'paddock', 'comment'])
        ));

        AllocationItem::create([
            'allocatable_type' => Allocation::class,
            'allocatable_id'   => $newAllocation->id,
            'bin_size'         => $allocation->item->bin_size,
            'no_of_bins'       => $request->input('no_of_bins'),
            'weight'           => $request->input('weight'),
        ]);

        foreach ($allocation->categories as $category) {
            CategoriesRelation::create([
                'category_id'        => $category->category_id,
                'categorizable_id'   => $newAllocation->id,
                'categorizable_type' => Allocation::class,
                'type'               => $category->type,
            ]);
        }

        $allocation->item->weight     = abs($allocation->item->weight - $request->input('weight'));
        $allocation->item->no_of_bins = abs($allocation->item->no_of_bins - $request->input('no_of_bins'));
        $allocation->item->save();

        NotificationHelper::duplicatedAction('Allocation', $allocation->id);

        return back();
    }

    private function getAllocations($buyerId, $search = '')
    {
        return Allocation::query()
            ->with([
                'item',
                'returnItems.returns',
                'categories.category',
                'grower:id,grower_name',
            ])
            ->withSum(['cuttingItems'], 'no_of_bins')
            ->withSum(['baggings'], 'no_of_bulk_bags_out')
            ->withSum(['baggings'], 'net_weight_bags_out')
            ->when($search, function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery
                        ->where('paddock', 'LIKE', "%{$search}%")
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('grower', 'grower_name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                });
            })
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }
}
