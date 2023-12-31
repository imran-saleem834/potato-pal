<?php

namespace App\Http\Controllers;

use App\Helpers\DeleteRecordsHelper;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Models\Reallocation;
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
        $reallocationBuyers = Reallocation::select('buyer_id')
            ->with(['buyer' => fn($query) => $query->select('id', 'name')])
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('buyer_id', 'LIKE', "%$search%")
                    ->orWhere('allocation_id', 'LIKE', "%$search%")
                    ->orWhere('no_of_bins', 'LIKE', "%$search%")
                    ->orWhere('weight', 'LIKE', "%$search%")
                    ->orWhere('comment', 'LIKE', "%$search%");
            })
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->map(function ($reallocation) {
                $reallocation->id = $reallocation->buyer_id;
                return $reallocation;
            });

        $firstBuyerId = $reallocationBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $reallocations = $this->getReallocations($inputBuyerId);
        if ($reallocations->isEmpty() && ((int)$inputBuyerId) !== ((int)$firstBuyerId)) {
            $reallocations = $this->getReallocations($firstBuyerId);
        }

        $users       = User::select(['id', 'name'])->get();
        $allocations = Allocation::query()
            ->with(['dispatches', 'reallocations', 'categories.category'])
            ->get()
            ->map(function ($allocation) {
                foreach ($allocation->dispatches as $dispatch) {
                    $allocation->no_of_bins -= $dispatch->no_of_bins;
                    $allocation->weight     -= $dispatch->weight;
                }
                foreach ($allocation->reallocations as $reallocation) {
                    $allocation->no_of_bins -= $reallocation->no_of_bins;
                    $allocation->weight     -= $reallocation->weight;
                }
                return $allocation;
            });

        return Inertia::render('Reallocation/Index', [
            'reallocationBuyers' => $reallocationBuyers,
            'single'             => $reallocations,
            'allocations'        => $allocations,
            'buyers'             => $users->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
            'filters'            => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReallocationRequest $request)
    {
        $reallocation = Reallocation::create($request->validated());

        NotificationHelper::addedAction('Reallocation', $reallocation->id);

        return to_route('reallocations.index', ['buyerId' => $reallocation->buyer_id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reallocations = $this->getReallocations($id);

        return response()->json($reallocations);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReallocationRequest $request, string $id)
    {
        $reallocation = Reallocation::find($id);
        $buyerId      = $reallocation->buyer_id;
        $reallocation->update($request->validated());
        $reallocation->save();

        NotificationHelper::updatedAction('Reallocation', $id);

        return to_route('reallocations.index', ['buyerId' => $buyerId]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reallocation = Reallocation::find($id);
        $buyerId      = $reallocation->buyer_id;
        $reallocation->delete();
        
        DeleteRecordsHelper::deleteDisaptachByReallocationId($id);

        NotificationHelper::deleteAction('Reallocation', $id);

        $isReallocationExists = Reallocation::where('buyer_id', $buyerId)->exists();
        if ($isReallocationExists) {
            return to_route('reallocations.index', ['buyerId' => $buyerId]);
        }

        return to_route('reallocations.index');
    }

    private function getReallocations($buyerId)
    {
        return Reallocation::with([
            'allocation.categories.category',
            'allocationBuyer' => fn($query) => $query->select('id', 'name'),
            'buyer'           => fn($query) => $query->select('id', 'name'),
            'buyer.categories.category',
        ])->where('buyer_id', $buyerId)->get();
    }
}
