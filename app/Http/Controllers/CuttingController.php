<?php

namespace App\Http\Controllers;

use App\Helpers\CategoriesHelper;
use App\Helpers\ReceivalHelper;
use App\Models\Category;
use App\Models\Cutting;
use App\Models\CuttingAllocation;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Allocation;
use Illuminate\Http\Request;
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
            ->with(['buyer' => fn($query) => $query->select('id', 'name')])
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('buyer_id', 'LIKE', "%$search%")
                    ->orWhere('cut_date', 'LIKE', "%$search%")
                    ->orWhere('cut_by', 'LIKE', "%$search%");
            })
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->map(function ($cutting) {
                $cutting->id = $cutting->buyer_id;
                return $cutting;
            });

        $firstBuyerId = $cuttingBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $cuttings = Cutting::with([
            'categories.category',
            'cuttingAllocations.allocation.categories.category',
            'buyer' => fn($query) => $query->select('id', 'name'),
            'buyer.categories.category',
        ])->where('buyer_id', $inputBuyerId)->get();

        if ($cuttings->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $cuttings = Cutting::with([
                'categories.category',
                'cuttingAllocations.allocation.categories.category',
                'buyer' => fn($query) => $query->select('id', 'name'),
                'buyer.categories.category',
            ])->where('buyer_id', $firstBuyerId)->get();
        }

        $users       = User::select(['id', 'name'])->get();
        $allocations = Allocation::with(['cuttings', 'categories.category'])->get();

        $allocations = $allocations->map(function ($allocation) {
            $allocation->allocation_id = $allocation->id;
            foreach ($allocation->cuttings as $cutting) {
                $allocation->no_of_bins -= $cutting->no_of_bins_after_cutting;
                $allocation->weight     -= $cutting->weight_after_cutting;
            }
            return $allocation;
        });

        return Inertia::render('Cutting/Index', [
            'cuttingBuyers' => $cuttingBuyers,
            'single'        => $cuttings,
            'allocations'   => $allocations,
            'categories'    => Category::where('type', 'fungicide')->get(),
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
            CuttingAllocation::create(
                array_merge(['cutting_id' => $cutting->id], $allocation)
            );
        }

        $inputs = $request->only(['fungicide']);
        CategoriesHelper::createRelationOfTypes($inputs, $cutting->id, Cutting::class);

        // ReceivalHelper::calculateRemainingReceivals($cutting->grower_id);

        NotificationHelper::addedAction('Cutting', $cutting->id);

        return to_route('cuttings.index', ['buyerId' => $cutting->buyer_id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cuttings = Cutting::with([
            'categories.category',
            'cuttingAllocations.allocation.categories.category',
            'buyer' => fn($query) => $query->select('id', 'name'),
            'buyer.categories.category',
        ])->where('buyer_id', $id)->get();

        return response()->json($cuttings);
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

        CuttingAllocation::where('cutting_id', $cutting->id)->delete();
        foreach ($request->validated('selected_allocations') as $allocation) {
            CuttingAllocation::create(
                array_merge(['cutting_id' => $cutting->id], $allocation)
            );
        }

        $inputs = $request->only(['fungicide']);
        CategoriesHelper::createRelationOfTypes($inputs, $cutting->id, Cutting::class);

        // ReceivalHelper::calculateRemainingReceivals($cutting->grower_id);

        NotificationHelper::updatedAction('Cutting', $id);

        return to_route('cuttings.index', ['buyerId' => $buyerId]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRealtions($id, Cutting::class);

        $cutting = Cutting::find($id);
        $buyerId = $cutting->buyer_id;
        $cutting->delete();

        CuttingAllocation::where('cutting_id', $id)->delete();

        // ReceivalHelper::calculateRemainingReceivals($growerId);

        NotificationHelper::deleteAction('Cutting', $id);

        return to_route('cuttings.index', ['buyerId' => $buyerId]);
    }
}
