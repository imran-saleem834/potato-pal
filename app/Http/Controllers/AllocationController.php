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
            'buyer'            => function ($query) {
                return $query->select('id', 'name');
            },
            'buyer.categories.category',
            'grower'           => function ($query) {
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
            'buyer'            => function ($query) {
                return $query->select('id', 'name');
            },
            'buyer.categories.category',
            'grower'           => function ($query) {
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
            'receivals' => function ($query) {
                return $query->select([
                    'id',
                    'user_id',
                    'oversize_bin_size',
                    'seed_bin_size',
                    'paddocks',
                    'created_at'
                ]);
            },
            'receivals.categories.category',
        ])->select(['id', 'name', 'grower_name', 'paddocks'])->get();
        return response()->json([
            'growers' => $users->map(function ($user) {
                $receivals = [];
                foreach ($user->receivals as $receival) {
                    $growerId    = $grower = $paddock = $seedTypeId = $seedType = $seedVarietyId = $seedVariety = '';
                    $seedClassId = $seedClass = $seedGenerationId = $seedGeneration = '';
                    foreach ($receival->categories as $category) {
                        if ($category->type === 'grower') {
                            $growerId = $category->category->id;
                            $grower   = $category->category->name;
                        }
                        if ($receival->paddocks) {
                            $paddock = $receival->paddocks[0];
                        }
                        if ($category->type === 'seed-type') {
                            $seedTypeId = $category->category->id;
                            $seedType   = $category->category->name;
                        }
                        if ($category->type === 'seed-variety') {
                            $seedVarietyId = $category->category->id;
                            $seedVariety   = $category->category->name;
                        }
                        if ($category->type === 'seed-class') {
                            $seedClassId = $category->category->id;
                            $seedClass   = $category->category->name;
                        }
                        if ($category->type === 'seed-generation') {
                            $seedGenerationId = $category->category->id;
                            $seedGeneration   = $category->category->name;
                        }
                    }
                    $key = "{$growerId}-{$seedTypeId}-{$seedVarietyId}-{$seedClassId}-{$seedGenerationId}";
                    if (isset($receivals[$key])) {
                        $receivals[$key]['oversize_bin_size'] = $receivals[$key]['oversize_bin_size'] + $receival->oversize_bin_size;
                        $receivals[$key]['seed_bin_size']     = $receivals[$key]['seed_bin_size'] + $receival->seed_bin_size;
                        continue;
                    }

                    $receivals[$key] = [
                        'user_id'           => $receival->user_id,
                        'created_at'        => $receival->created_at,
                        'oversize_bin_size' => $receival->oversize_bin_size,
                        'seed_bin_size'     => $receival->seed_bin_size,
                        'grower'            => $grower,
                        'paddock'           => $paddock,
                        'seed-type'         => $seedType,
                        'seed-variety'      => $seedVariety,
                        'seed-class'        => $seedClass,
                        'seed-generation'   => $seedGeneration,
                    ];
                }

                return [
                    'value'     => $user->id,
                    'label'     => $user->grower_name ? "$user->name ($user->grower_name)" : $user->name,
                    'receivals' => $receivals,
                ];
            }),
            'buyers'  => $users->map(function ($user) {
                return ['value' => $user->id, 'label' => $user->name];
            }),
        ]);
    }
}
