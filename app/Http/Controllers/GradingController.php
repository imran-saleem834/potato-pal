<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Unload;
use App\Models\Grading;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\GradingRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GradingController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $navBuyers = $this->getListOfModelUsers($request->input('buyer'));

        $firstUserId = $navBuyers->first()->user_id ?? '';
        $inputUserId = $request->input('userId', $firstUserId);

        $gradings = $this->getGradings($inputUserId, $request->input('search'));
        if ($gradings->isEmpty() && ((int) $inputUserId) !== ((int) $firstUserId)) {
            $gradings = $this->getGradings($firstUserId, $request->input('search'));
        }

        return Inertia::render('Grading/Index', [
            'navBuyers' => $navBuyers,
            'single'    => $gradings,
            'growers'    => fn () => BuyerHelper::getAvailableGrowers(),
            'buyers'     => fn () => BuyerHelper::getAvailableBuyers(),
            'filters'   => $request->only(['search', 'buyer']),
        ]);
    }

    public function allocations(Request $request, $id)
    {
        $allocations = Allocation::query()
            ->with(['item', 'categories.category', 'grower:id,grower_name'])
            ->where('buyer_id', $id)
            ->get();

        return response()->json($allocations->toArray());
    }

    public function unloads(Request $request, $id)
    {
        $unloads = Unload::query()
            ->with([
                'categories.category',
                'receival:id,grower_id,paddocks',
                'receival.categories.category',
                'receival.grower:id,grower_name',
            ])
            ->whereRelation('receival', function (Builder $query) use ($id) {
                return $query->where('grower_id', $id)
                    ->orWhere('status', 'completed');
            })
            ->get();

        return response()->json($unloads->toArray());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GradingRequest $request)
    {
        $inputs = $this->getGradeableInputs($request);

        $grading = Grading::create(array_merge($request->validated(), $inputs));

        NotificationHelper::addedAction('Grading', $grading->id);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GradingRequest $request, string $id)
    {
        $inputs = $this->getGradeableInputs($request);

        $grading = Grading::find($id);
        $grading->update(array_merge($request->validated(), $inputs));
        $grading->save();

        NotificationHelper::updatedAction('Grading', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Grading::destroy($id);

        NotificationHelper::deleteAction('Grade', $id);

        return back();
    }

    private function getGradings($userId, $search = '')
    {
        return Grading::query()
            ->with([
                'gradeable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Unload::class     => [
                            'categories.category',
                            'receival.grower:id,grower_name',
                            'receival.categories.category',
                        ],
                        Allocation::class => [
                            'item',
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                    ]);
                },
            ])
            ->when($search, function (Builder $query, $search) {
                return $query->where(function (Builder $query) use ($search) {
                    return $query
                        ->where('no_of_crew', 'LIKE', "%{$search}%")
                        ->orWhere('comments', 'LIKE', "%{$search}%")
                        ->orWhereHasMorph('gradeable', [Allocation::class, Unload::class], function (Builder $query, string $type) use ($search) {
                            if ($type === Allocation::class) {
                                return $query->where('paddock', 'LIKE', "%{$search}%")
                                    ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%");
                            }

                            return $query->whereRelation('receival', function (Builder $query) use ($search) {
                                return $query
                                    ->where('paddocks', 'LIKE', "%{$search}%")
                                    ->orWhere('comments', 'LIKE', "%$search%")
                                    ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%")
                                    ->orWhereRelation('grower', function (Builder $query) use ($search) {
                                        return $query->where('name', 'LIKE', "%{$search}%")
                                            ->orWhere('grower_name', 'LIKE', "%{$search}%");
                                    });
                            });
                        });
                });
            })
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }

    private function getListOfModelUsers($search = '')
    {
        return Grading::query()
            ->select('user_id')
            ->with([
                'user:id,buyer_name,grower_name',
                'user.categories.category',
            ])
            ->when($search, function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery
                        ->whereRelation('user', function (Builder $query) use ($search) {
                            return $query->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('user_name', 'LIKE', "%{$search}%")
                                ->orWhere('grower_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('user.categories.category', 'name', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->groupBy('user_id')
            ->get()
            ->sortBy(fn ($item) => $item->user->buyer_name ?? $item->user->grower_name)
            ->map(function ($item) {
                $item->id = $item->user_id;

                return $item;
            });
    }

    private function getGradeableInputs(GradingRequest $request)
    {
        if ($request->validated('type') === 'allocation') {
            $allocation = $request->validated('selected_allocation');
            $inputs     = [
                'gradeable_id'   => $allocation['id'],
                'gradeable_type' => Allocation::class,
            ];
        } else {
            $unload = $request->validated('selected_unload');
            $inputs = [
                'gradeable_id'   => $unload['id'],
                'gradeable_type' => Unload::class,
            ];
        }

        return $inputs;
    }
}
