<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Unload;
use App\Models\Receival;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Helpers\CategoriesHelper;
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
        $allocationBuyers = Allocation::select(['buyer_id'])
            ->with(['buyer:id,buyer_name', 'buyer.categories.category'])
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->map(function ($allocation) {
                $allocation->id = $allocation->buyer_id;

                return $allocation;
            });

        $firstBuyerId = $allocationBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $allocations = $this->getAllocations($inputBuyerId, $request->input('search'));
        if ($allocations->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $allocations = $this->getAllocations($firstBuyerId, $request->input('search'));
        }

        return Inertia::render('Allocation/Index', [
            'allocationBuyers' => $allocationBuyers,
            'single'           => $allocations,
            'growers'          => fn () => $this->growers(),
            'buyers'           => fn () => $this->buyers(),
            'filters'          => $request->only(['search']),
        ]);
    }

    private function growers()
    {
        $growers = User::query()
            ->with(['remainingReceivals'])
            ->select(['id', 'grower_name'])
            ->whereJsonContains('role', 'grower')
            ->get();

        $receivals = collect([]);
        foreach ($growers as $grower) {
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
        }

        return $growers->map(function ($grower) use ($receivals) {
            return [
                'value'     => $grower->id,
                'label'     => $grower->grower_name,
                'receivals' => $receivals->where('grower_id', $grower->id)->values(),
            ];
        });
    }

    private function buyers()
    {
        return User::query()
            ->select(['id', 'buyer_name'])
            ->whereJsonContains('role', 'buyer')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->buyer_name]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllocationRequest $request)
    {
        $allocation = Allocation::create($request->validated());

        $inputs = $request->only([
            'grower_group',
            'seed_type',
            'seed_variety',
            'seed_generation',
            'seed_class',
        ]);
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
        $buyerId    = $allocation->buyer_id;
        $allocation->update($request->validated());
        $allocation->save();

        $inputs = $request->only([
            'grower_group',
            'seed_type',
            'seed_variety',
            'seed_generation',
            'seed_class',
        ]);
        CategoriesHelper::createRelationOfTypes($inputs, $allocation->id, Allocation::class);

        ReceivalHelper::calculateRemainingReceivals($allocation->grower_id);

        NotificationHelper::updatedAction('Allocation', $id);

        return to_route('allocations.index', ['buyerId' => $buyerId]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRelations($id, Allocation::class);

        $allocation = Allocation::find($id);
        $buyerId    = $allocation->buyer_id;
        $growerId   = $allocation->grower_id;

        DeleteRecordsHelper::deleteAllocation($id);

        NotificationHelper::deleteAction('Allocation', $id);

        ReceivalHelper::calculateRemainingReceivals($growerId);

        $isAllocationExists = Allocation::where('buyer_id', $buyerId)->exists();
        if ($isAllocationExists) {
            return to_route('allocations.index', ['buyerId' => $buyerId]);
        }

        return to_route('allocations.index');
    }

    private function getAllocations($buyerId, $search = '')
    {
        return Allocation::query()
            ->with([
                'categories.category',
                'grower' => fn ($query) => $query->select('id', 'name', 'grower_name'),
            ])
            ->withSum(['cuttings'], 'no_of_bins')
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('paddock', 'LIKE', "%{$search}%")
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhere('no_of_bins', 'LIKE', "%{$search}%")
                        ->orWhere('bin_size', 'LIKE', "%{$search}%")
                        ->orWhereRaw("CONCAT(`weight`, ' kg') LIKE '%{$search}%'")
                        ->orWhereRelation('grower', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('grower_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('categories.category', function (Builder $query) use ($search) {
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
