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
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cuttingBuyers = Cutting::select('buyer_id')
            ->with(['buyer' => fn($query) => $query->select(['id', 'name', 'buyer_name']), 'buyer.categories.category'])
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
        if ($cuttings->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $cuttings = $this->getCuttings($firstBuyerId, $request->input('search'));
        }

        $users       = User::select(['id', 'name'])->get();
        $allocations = Allocation::with(['dispatches', 'cuttings', 'categories.category'])
            ->get()
            ->map(function ($allocation) {
                $allocation->allocation_id = $allocation->id;
                foreach ($allocation->dispatches as $dispatch) {
                    $allocation->no_of_bins -= $dispatch->no_of_bins;
                    $allocation->weight     -= $dispatch->weight;
                }
                foreach ($allocation->cuttings as $cutting) {
                    $allocation->no_of_bins -= $cutting->no_of_bins_before_cutting;
                    $allocation->weight     -= $cutting->weight_before_cutting;
                }
                return $allocation;
            });

        return Inertia::render('Cutting/Index', [
            'cuttingBuyers' => $cuttingBuyers,
            'single'        => $cuttings,
            'allocations'   => $allocations,
            'categories'    => fn() => Category::where('type', 'fungicide')->get(),
            'buyers'        => $users->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
            'filters'       => $request->only(['search']),
        ]);
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

        $inputs = $request->only(['fungicide']);
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
        $buyerId = $cutting->buyer_id;
        $cutting->update($request->validated());
        $cutting->save();

        $cuttingAllocations = [];
        foreach ($request->validated('selected_allocations') as $cuttingAllocation) {
            if (isset($cuttingAllocation['id'])) {
                $cuttingAllocations[] = $cuttingAllocation['id'];
                CuttingAllocation::find($cuttingAllocation['id'])->update($cuttingAllocation);
            } else {
                CuttingAllocation::create(array_merge(['cutting_id' => $cutting->id], $cuttingAllocation));
            }
        }
        CuttingAllocation::where('cutting_id', $cutting->id)->whereNotIn('id', $cuttingAllocations)->delete();

        $inputs = $request->only(['fungicide']);
        CategoriesHelper::createRelationOfTypes($inputs, $cutting->id, Cutting::class);

        NotificationHelper::updatedAction('Cutting', $id);

        return to_route('cuttings.index', ['buyerId' => $buyerId]);
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
                'buyer' => fn($query) => $query->select('id', 'name'),
                'buyer.categories.category',
            ])
            ->when($search, function ($query, $search) {
                 return $query->where(function ($subQuery) use ($search) {
                     return $subQuery
                         ->where('comment', 'LIKE', "%{$search}%")
                         ->orWhere('cut_by', 'LIKE', "%{$search}%")
                         ->orWhere('cut_date', 'LIKE', "%{$search}%")
                         ->orWhereRelation('cuttingAllocations', function (Builder $query) use ($search) {
                             return $query
                                 ->where('no_of_bins_before_cutting', 'LIKE', "%{$search}%")
                                 ->orWhere('no_of_bins_after_cutting', 'LIKE', "%{$search}%")
                                 ->orWhereRaw("CONCAT(`weight_before_cutting`, ' kg') LIKE '%{$search}%'")
                                 ->orWhereRaw("CONCAT(`weight_after_cutting`, ' kg') LIKE '%{$search}%'");
                         })
                         ->orWhereRelation('categories.category', function (Builder $query) use ($search) {
                             return $query->where('name', 'LIKE', "%{$search}%");
                         })
                         ->orWhereRelation('cuttingAllocations.allocation.categories.category', function (Builder $query) use ($search) {
                             return $query->where('name', 'LIKE', "%{$search}%");
                         });
                 });
            })
            ->where('buyer_id', $buyerId)
            ->paginate(10)
            ->withQueryString();

        tap($cuttings)->map(function ($cutting) {
            $cutting->cuttingAllocations = $cutting->cuttingAllocations->map(function ($cuttingAllocation) {
                $cuttingAllocation->allocation->no_of_bins -= $cuttingAllocation->no_of_bins_before_cutting;
                $cuttingAllocation->allocation->weight     -= $cuttingAllocation->weight_before_cutting;
            });
            return $cutting;
        });

        return $cuttings;
    }
}
