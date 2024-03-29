<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Dispatch;
use App\Models\Allocation;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\DispatchRequest;
use Illuminate\Database\Eloquent\Builder;

class DispatchController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dispatchBuyers = Dispatch::select('buyer_id')
            ->with([
                'buyer' => fn ($query) => $query->select(['id', 'name', 'buyer_name']),
                'buyer.categories.category',
            ])
            ->latest()
            ->groupBy('buyer_id')
            ->get()
            ->map(function ($dispatch) {
                $dispatch->id = $dispatch->buyer_id;

                return $dispatch;
            });

        $firstBuyerId = $dispatchBuyers->first()->buyer_id ?? '';
        $inputBuyerId = $request->input('buyerId', $firstBuyerId);

        $dispatchs = $this->getDispatchs($inputBuyerId, $request->input('search'));
        if ($dispatchs->isEmpty() && ((int) $inputBuyerId) !== ((int) $firstBuyerId)) {
            $dispatchs = $this->getDispatchs($firstBuyerId, $request->input('search'));
        }

        $allocations   = Allocation::query()
            ->with(['reallocations', 'dispatches', 'categories.category'])
            ->get()
            ->map(function ($allocation) {
                foreach ($allocation->reallocations as $reallocation) {
                    $allocation->no_of_bins -= $reallocation->no_of_bins;
                    $allocation->weight     -= $reallocation->weight;
                }
                foreach ($allocation->dispatches as $dispatch) {
                    $allocation->no_of_bins -= $dispatch->no_of_bins;
                    $allocation->weight     -= $dispatch->weight;
                }

                return $allocation;
            });
        $reallocations = Reallocation::query()
            ->with(['dispatches', 'allocation.categories.category'])
            ->get()
            ->map(function ($reallocation) {
                foreach ($reallocation->dispatches as $dispatch) {
                    $reallocation->no_of_bins -= $dispatch->no_of_bins;
                    $reallocation->weight     -= $dispatch->weight;
                }

                return $reallocation;
            });

        return Inertia::render('Dispatch/Index', [
            'dispatchBuyers' => $dispatchBuyers,
            'single'         => $dispatchs,
            'allocations'    => fn () => $allocations,
            'reallocations'  => fn () => $reallocations,
            'buyers'         => fn () => $this->buyers(),
            'filters'        => $request->only(['search']),
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
    public function store(DispatchRequest $request)
    {
        $dispatch = Dispatch::create($request->validated());

        NotificationHelper::addedAction('Dispatch', $dispatch->id);

        return to_route('dispatches.index', ['buyerId' => $dispatch->buyer_id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DispatchRequest $request, string $id)
    {
        $dispatch = Dispatch::find($id);
        $buyerId  = $dispatch->buyer_id;
        $dispatch->update($request->validated());
        $dispatch->save();

        NotificationHelper::updatedAction('Dispatch', $id);

        return to_route('dispatches.index', ['buyerId' => $buyerId]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dispatch = Dispatch::find($id);
        $buyerId  = $dispatch->buyer_id;
        $dispatch->delete();

        NotificationHelper::deleteAction('Dispatch', $id);

        $isDispatchExists = Dispatch::where('buyer_id', $buyerId)->exists();
        if ($isDispatchExists) {
            return to_route('dispatches.index', ['buyerId' => $buyerId]);
        }

        return to_route('dispatches.index');
    }

    private function getDispatchs($buyerId, $search = '')
    {
        return Dispatch::query()
            ->with([
                'allocationBuyer' => fn ($query) => $query->select('id', 'name'),
                'allocation.categories.category',
                'reallocation.allocation.categories.category',
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('comment', 'LIKE', "%{$search}%")
                        ->orWhere('no_of_bins', 'LIKE', "%{$search}%")
                        ->orWhereRaw("CONCAT(`weight`, ' kg') LIKE '%{$search}%'")
                        ->orWhereRelation('allocationBuyer', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('allocation', function (Builder $query) use ($search) {
                            return $query->where('paddock', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('reallocation.allocation', function (Builder $query) use ($search) {
                            return $query->where('paddock', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('allocation.categories.category', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('reallocation.allocation.categories.category', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->where('buyer_id', $buyerId)
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }
}
