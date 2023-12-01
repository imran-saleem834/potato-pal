<?php

namespace App\Http\Controllers;

use App\Models\Receival;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
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
        $allocations = Allocation::select('id', 'buyer_id')
            ->with([
                'buyer' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('buyer_id', 'LIKE', "%$search%")
                    ->orWhere('allocated_bins', 'LIKE', "%$search%")
                    ->orWhere('allocated_tonnes', 'LIKE', "%$search%")
                    ->orWhere('tonnes_available_receivals', 'LIKE', "%$search%")
                    ->orWhere('bins_before_cutting', 'LIKE', "%$search%")
                    ->orWhere('tonnes_before_cutting', 'LIKE', "%$search%")
                    ->orWhere('cutting_date', 'LIKE', "%$search%")
                    ->orWhere('bins_after_cutting', 'LIKE', "%$search%")
                    ->orWhere('tonnes_after_cutting', 'LIKE', "%$search%")
                    ->orWhere('reallocated_buyer_id', 'LIKE', "%$search%")
                    ->orWhere('tonnes_reallocated', 'LIKE', "%$search%")
                    ->orWhere('bins_reallocated', 'LIKE', "%$search%");
            })
            ->latest()
            ->get();

        $allocationId = $request->input('allocationId', $allocations->first()->id ?? 0);

        $allocation = Allocation::with([
            'buyer' => function ($query) {
                return $query->select('id', 'name');
            },
            'buyer.categories.category',
            'grower' => function ($query) {
                return $query->select('id', 'name');
            },
            'reallocatedBuyer' => function ($query) {
                return $query->select('id', 'name');
            },
        ])->find($allocationId);

        return Inertia::render('Allocation/Index', [
            'allocations' => $allocations,
            'single'      => $allocation,
            'filters'     => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllocationRequest $request)
    {
        $inputs = $request->validated();

        $user = Allocation::create($inputs);

        NotificationHelper::addedAction('Allocation', $user->id);

        return to_route('allocations.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $allocation = Allocation::with([
            'buyer' => function ($query) {
                return $query->select('id', 'name');
            },
            'buyer.categories.category',
            'grower' => function ($query) {
                return $query->select('id', 'name');
            },
            'reallocatedBuyer' => function ($query) {
                return $query->select('id', 'name');
            },
        ])->find($id);

        return response()->json($allocation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AllocationRequest $request, string $id)
    {
        $inputs = $request->validated();

        info(print_r($inputs, true));

        $allocation = Allocation::find($id);
        $allocation->update($inputs);
        $allocation->save();

        NotificationHelper::updatedAction('Allocation', $id);

        return to_route('allocations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Allocation::destroy($id);

        NotificationHelper::deleteAction('Allocation', $id);

        return to_route('allocations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getUsers()
    {
        $users = User::with([
            'receivals' => function($query) {
                return $query->select(['id', 'user_id', 'oversize_bin_size', 'seed_bin_size', 'created_at']);
            },
            'receivals.categories' => function($query) {
                return $query->where('type', 'seed-generation');
            },
            'receivals.categories.category',
        ])->select(['id', 'name', 'grower_name'])->get();
        return response()->json([
            'growers' => $users->map(function ($user) {
                return ['value' => $user->id, 'label' => $user->grower_name ? "$user->name ($user->grower_name)" : $user->name, 'receivals' => $user->receivals];
            }),
            'buyers' => $users->map(function ($user) {
                return ['value' => $user->id, 'label' => $user->name];
            }),
        ]);
    }
}
