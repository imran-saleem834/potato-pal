<?php

namespace App\Http\Controllers;

use App\Helpers\ReceivalHelper;
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
                    ->orWhere('grower_id', 'LIKE', "%$search%")
                    ->orWhere('unique_key', 'LIKE', "%$search%")
                    ->orWhere('allocated_type', 'LIKE', "%$search%")
                    ->orWhere('allocated_bins', 'LIKE', "%$search%")
                    ->orWhere('allocated_tonnes', 'LIKE', "%$search%")
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

        $users = User::with([
            'remainingReceivals',
            'receivals.categories.category',
        ])->select(['id', 'name', 'grower_name', 'paddocks'])->get();

        $growers = $users->map(function ($user) {
            $user->remainingReceivals = $user->remainingReceivals->map(function ($remainingReceival) {

                $receival = Receival::with('categories')->find($remainingReceival->receival_id[0]);

                [
                    $paddock,
                    $growerGroup,
                    $seedType,
                    $seedVariety,
                    $seedClass,
                    $seedGeneration
                ] = ReceivalHelper::getCategoryNames($receival);

                $remainingReceival->grower_group    = $growerGroup;
                $remainingReceival->paddock         = $paddock;
                $remainingReceival->seed_type       = $seedType;
                $remainingReceival->seed_variety    = $seedVariety;
                $remainingReceival->seed_class      = $seedClass;
                $remainingReceival->seed_generation = $seedGeneration;

                return $remainingReceival;
            });

            return [
                'value'     => $user->id,
                'label'     => $user->grower_name ? "$user->name ($user->grower_name)" : $user->name,
                'receivals' => $user->remainingReceivals,
            ];
        });

        return Inertia::render('Allocation/Index', [
            'allocations' => $allocations,
            'single'      => $allocation,
            'growers'     => $growers,
            'buyers'      => $users->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
            'filters'     => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllocationRequest $request)
    {
        $allocation = Allocation::create($request->validated());

        ReceivalHelper::calculateRemainingReceivals($allocation->grower_id);

        NotificationHelper::addedAction('Allocation', $allocation->id);

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
        $allocation = Allocation::find($id);
        $allocation->update($request->validated());
        $allocation->save();

        ReceivalHelper::calculateRemainingReceivals($allocation->grower_id);

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
}
