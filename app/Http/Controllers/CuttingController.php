<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Cutting;
use App\Models\Category;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Models\CuttingAllocation;
use App\Helpers\NotificationHelper;
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
        $cuttingBuyers = Cutting::select('buyer_id')
            ->with([
                'buyer:id,name,buyer_name',
                'buyer.categories.category',
            ])
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->map(function ($cutting) {
                $cutting->id = $cutting->buyer_id;

                return $cutting;
            });

        $firstBuyerId = $cuttingBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $cuttings = $this->getCuttings($inputBuyerId, $request->input('search'));
        if ($cuttings->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $cuttings = $this->getCuttings($firstBuyerId, $request->input('search'));
        }

        $allocations = Allocation::with(['dispatches', 'cuttings', 'categories.category'])
            ->get()
            ->map(function ($allocation) {
                $allocation->allocation_id        = $allocation->id;
                $allocation->available_no_of_bins = $allocation->no_of_bins;
                foreach ($allocation->dispatches as $dispatch) {
                    $allocation->available_no_of_bins -= $dispatch->no_of_bins;
                }
                foreach ($allocation->cuttings as $cutting) {
                    $allocation->available_no_of_bins -= $cutting->no_of_bins;
                }

                return $allocation;
            });

        return Inertia::render('Cutting/Index', [
            'cuttingBuyers' => $cuttingBuyers,
            'single'        => $cuttings,
            'allocations'   => $allocations,
            'categories'    => fn () => Category::whereIn('type', ['cool-store', 'fungicide'])->get(),
            'buyers'        => fn () => $this->buyers(),
            'filters'       => $request->only(['search']),
        ]);
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
    public function store(CuttingRequest $request)
    {
        $cutting = Cutting::create($request->validated());

        foreach ($request->validated('selected_allocations') as $allocation) {
            CuttingAllocation::create(array_merge(['cutting_id' => $cutting->id], $allocation));
        }

        $inputs = $request->only(['cool_store', 'fungicide']);
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

        $cuttingAllocations = [];
        foreach ($request->validated('selected_allocations') as $cuttingAllocation) {
            if (isset($cuttingAllocation['id'])) {
                $cutAllocation = CuttingAllocation::find($cuttingAllocation['id']);
                $cutAllocation->update($cuttingAllocation);
                $cutAllocation->save();
            } else {
                $cutAllocation = CuttingAllocation::create(array_merge(['cutting_id' => $cutting->id],
                    $cuttingAllocation));
            }
            $cuttingAllocations[] = $cutAllocation->id;
        }

        // Delete those who where not comes to update or create
        CuttingAllocation::query()
            ->where('cutting_id', $cutting->id)
            ->whereNotIn('id', $cuttingAllocations)
            ->delete();

        $inputs = $request->only(['cool_store', 'fungicide']);
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

        $cutting = Cutting::find($id);
        $buyerId = $cutting->buyer_id;
        $cutting->delete();

        CuttingAllocation::where('cutting_id', $id)->delete();

        NotificationHelper::deleteAction('Cutting', $id);

        $isCuttingExists = Cutting::where('buyer_id', $buyerId)->exists();

        if ($isCuttingExists) {
            return to_route('cuttings.index', ['buyerId' => $buyerId]);
        }

        return to_route('cuttings.index');
    }

    private function getCuttings($buyerId, $search = '')
    {
        $cuttings = Cutting::query()
            ->with([
                'categories.category',
                'cuttingAllocations.allocation.categories.category',
                'cuttingAllocations.allocation.dispatches:id,allocation_id,no_of_bins',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('half_tonnes', 'LIKE', "%{$search}%")
                        ->orWhere('one_tonnes', 'LIKE', "%{$search}%")
                        ->orWhere('two_tonnes', 'LIKE', "%{$search}%")
                        ->orWhere('cut_date', 'LIKE', "%{$search}%")
                        ->orWhere('comment', 'LIKE', "%{$search}%")
                        ->orWhereRelation('categories.category', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('cuttingAllocations.allocation.categories.category', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);

        $availableBins = [];
        tap($cuttings)->map(function ($cutting) use (&$availableBins) {
            $cutting->cuttingAllocations->each(function ($ca) use (&$availableBins) {
                $availableBins[$ca->allocation->id] = $ca->allocation->no_of_bins;
            });

            return $cutting;
        });
        tap($cuttings)->map(function ($cutting) use (&$availableBins) {
            $cutting->cuttingAllocations->each(function ($ca) use (&$availableBins) {
                $availableBins[$ca->allocation->id] -= $ca->no_of_bins;
            });

            return $cutting;
        });
        tap($cuttings)->map(function ($cutting) use (&$availableBins) {
            $cutting->cuttingAllocations = $cutting->cuttingAllocations->map(function ($ca) use (&$availableBins) {
                $ca->allocation->available_no_of_bins = $availableBins[$ca->allocation->id];

                return $ca;
            });

            return $cutting;
        });

        return $cuttings;
    }
}
