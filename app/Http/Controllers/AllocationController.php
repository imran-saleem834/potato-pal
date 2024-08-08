<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Arr;
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
use App\Http\Requests\DuplicateRequest;
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
            ->whereJsonContains('access', 'grower')
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

        $receival = $request->validated('select_receival');
        $binSize = $receival['bin_size'];

        AllocationItem::create([
            'allocatable_type' => Allocation::class,
            'allocatable_id'   => $allocation->id,
            'weight'           => $request->input('weight'),
            'half_tonnes'      => $binSize === 500 ?  $request->input('no_of_bins') : 0,
            'one_tonnes'       => $binSize === 1000 ? $request->input('no_of_bins') : 0,
            'two_tonnes'       => $binSize === 2000 ? $request->input('no_of_bins') : 0,
        ]);

        $inputs = $request->only(Allocation::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $allocation->id, Allocation::class);

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
        $allocation->update($request->validated());
        $allocation->save();

        $receival = $request->validated('select_receival');
        $binSize = $receival['bin_size'];

        AllocationItem::updateOrCreate(
            ['allocatable_type' => Allocation::class, 'allocatable_id' => $allocation->id, 'returned_id' => null],
            [
                'weight'      => $request->input('weight'),
                'half_tonnes' => $binSize === 500 ?  $request->input('no_of_bins') : 0,
                'one_tonnes'  => $binSize === 1000 ? $request->input('no_of_bins') : 0,
                'two_tonnes'  => $binSize === 2000 ? $request->input('no_of_bins') : 0,
            ]
        );

        $inputs = $request->only(Allocation::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $allocation->id, Allocation::class);

        ReceivalHelper::calculateRemainingReceivals($allocation->grower_id);

        NotificationHelper::updatedAction('Allocation', $id);

        return to_route('allocations.index', ['buyerId' => $allocation->buyer_id]);
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

    public function duplicate(DuplicateRequest $request)
    {
        $inputs           = $request->validated();
        $allocationInputs = $inputs['allocations'];
        $allocationIds    = Arr::pluck($allocationInputs, ['id']);
        $allocations      = Allocation::query()
            ->with(['categories', 'item'])
            ->whereIn('id', $allocationIds)
            ->get()
            ->map(function ($allocation) use ($allocationInputs) {
                $inputs = Arr::first($allocationInputs, fn($input) => $input['id'] === $allocation->id);

                $allocation->half_tonnes = $inputs['half_tonnes'];
                $allocation->one_tonnes  = $inputs['one_tonnes'];
                $allocation->two_tonnes  = $inputs['two_tonnes'];
                $allocation->weight      = $inputs['weight'];
                return $allocation;
            });

        $mergeAllocation = Allocation::create(array_merge(
            $request->safe()->only(['buyer_id']),
            $allocations->first()->only(['grower_id', 'paddock', 'comment']),
            ['is_merger' => 1]
        ));

        AllocationItem::create([
            'allocatable_type' => Allocation::class,
            'allocatable_id'   => $mergeAllocation->id,
            'weight'           => $inputs['weight'],
            'half_tonnes'      => $inputs['half_tonnes'],
            'one_tonnes'       => $inputs['one_tonnes'],
            'two_tonnes'       => $inputs['two_tonnes'],
        ]);

        foreach ($allocations->first()->categories as $category) {
            CategoriesRelation::create([
                'category_id'        => $category->category_id,
                'categorizable_id'   => $mergeAllocation->id,
                'categorizable_type' => Allocation::class,
                'type'               => $category->type,
            ]);
        }

        foreach ($allocations as $allocation) {
            $newAllocation = Allocation::create(array_merge(
                $allocation->only(['buyer_id', 'grower_id', 'unique_key', 'paddock', 'comment']),
                ['merger_id' => $mergeAllocation->id]
            ));

            AllocationItem::create([
                'allocatable_type' => Allocation::class,
                'allocatable_id'   => $newAllocation->id,
                'weight'           => $allocation->weight,
                'half_tonnes'      => $allocation->half_tonnes,
                'one_tonnes'       => $allocation->one_tonnes,
                'two_tonnes'       => $allocation->two_tonnes,
            ]);

            foreach ($allocation->categories as $category) {
                CategoriesRelation::create([
                    'category_id'        => $category->category_id,
                    'categorizable_id'   => $newAllocation->id,
                    'categorizable_type' => Allocation::class,
                    'type'               => $category->type,
                ]);
            }

            $allocation->item->weight      = $allocation->item->weight      - $allocation->weight;
            $allocation->item->half_tonnes = $allocation->item->half_tonnes - $allocation->half_tonnes;
            $allocation->item->one_tonnes  = $allocation->item->one_tonnes  - $allocation->one_tonnes;
            $allocation->item->two_tonnes  = $allocation->item->two_tonnes  - $allocation->two_tonnes;
            $allocation->item->save();
        }

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
            ->withSum(['cuttingItems'], 'from_half_tonnes')
            ->withSum(['cuttingItems'], 'from_one_tonnes')
            ->withSum(['cuttingItems'], 'from_two_tonnes')
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
