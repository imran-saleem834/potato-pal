<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
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
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allocationBuyers = Allocation::select('buyer_id')
            ->with(['buyer' => fn($query) => $query->select('id', 'name'), 'buyer.categories.category'])
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
        if ($allocations->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $allocations = $this->getAllocations($firstBuyerId, $request->input('search'));
        }

        $users = User::with([
            'remainingReceivals',
            'receivals.categories.category',
        ])->select(['id', 'name', 'grower_name', 'paddocks'])->get();

        $growers = $users->map(function ($user) {
            $user->remainingReceivals = $user->remainingReceivals->filter(function ($remainingReceival) {
                return !empty($remainingReceival->receival_id);
            })->map(function ($remainingReceival) {
                $receival = Receival::with(['categories.category'])->find($remainingReceival->receival_id[0]);

                [
                    $binSize,
                    $paddock,
                    $growerGroup,
                    $seedType,
                    $seedVariety,
                    $seedClass,
                    $seedGeneration
                ] = ReceivalHelper::getCategoryNames($receival);

                $remainingReceival->bin_size        = $binSize;
                $remainingReceival->grower_group    = $growerGroup;
                $remainingReceival->paddock         = $paddock;
                $remainingReceival->seed_type       = $seedType;
                $remainingReceival->seed_variety    = $seedVariety;
                $remainingReceival->seed_class      = $seedClass;
                $remainingReceival->seed_generation = $seedGeneration;
                $remainingReceival->categories      = $receival->categories;

                return $remainingReceival;
            });

            $user->remainingReceivals = $user->remainingReceivals->sortBy(function ($remainingReceival) {
                return max($remainingReceival->receival_id);
            })->values();

            return [
                'value'     => $user->id,
                'label'     => $user->grower_name ? "$user->name ($user->grower_name)" : $user->name,
                'receivals' => $user->remainingReceivals,
            ];
        });

        return Inertia::render('Allocation/Index', [
            'allocationBuyers' => $allocationBuyers,
            'single'           => $allocations,
            'growers'          => fn () => $growers,
            'buyers'           => fn () => $users->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
            'filters'          => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllocationRequest $request)
    {
        $allocation = Allocation::create($request->validated());

        $inputs = $request->only([
            'grower',
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
            'grower',
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
        CategoriesHelper::deleteCategoryRealtions($id, Allocation::class);

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
                'grower' => fn($query) => $query->select('id', 'name'),
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('paddock', 'LIKE', "%{$search}%")
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhere('no_of_bins', 'LIKE', "%{$search}%")
                        ->orWhereRaw("CONCAT(`bin_size`, ' Tonnes') LIKE '%{$search}%'")
                        ->orWhereRaw("CONCAT(`weight`, ' Tonnes') LIKE '%{$search}%'")
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
            ->paginate(10)
            ->withQueryString();
    }
}
