<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Sizing;
use App\Models\Unload;
use App\Models\Category;
use App\Models\Allocation;
use App\Helpers\BuyerHelper;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\SizingRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SizingController extends Controller
{
    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $navBuyers = $this->getListOfModelUsers($request->input('buyer'));

        $firstUserId = $navBuyers->first()->user_id ?? '';
        $inputUserId = $request->input('userId', $firstUserId);

        $sizings = $this->getSizings($inputUserId, $request->input('search'));
        if ($sizings->isEmpty() && ((int)$inputUserId) !== ((int)$firstUserId)) {
            $sizings = $this->getSizings($firstUserId, $request->input('search'));
        }

        return Inertia::render('Sizing/Index', [
            'navBuyers'  => $navBuyers,
            'single'     => $sizings,
            'categories' => fn() => Category::whereIn('type', Sizing::CATEGORY_TYPES)->get(),
            'growers'    => fn() => BuyerHelper::getAvailableGrowers(),
            'buyers'     => fn() => BuyerHelper::getAvailableBuyers(),
            'filters'    => $request->only(['search', 'buyer']),
        ]);
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
    public function store(SizingRequest $request)
    {
        if ($request->validated('type') === 'allocation') {
            $allocation = $request->validated('selected_allocation');
            $inputs     = [
                'sizeable_id'   => $allocation['id'],
                'sizeable_type' => Allocation::class,
            ];
        } else {
            $unload = $request->validated('selected_unload');
            $inputs = [
                'sizeable_id'   => $unload['id'],
                'sizeable_type' => Unload::class,
            ];
        }

        $sizing = Sizing::create(array_merge($request->validated(), $inputs));

        $inputs = $request->safe()->only(Sizing::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $sizing->id, Sizing::class);

        NotificationHelper::addedAction('Sizing', $sizing->id);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SizingRequest $request, string $id)
    {
        if ($request->validated('type') === 'allocation') {
            $allocation = $request->validated('selected_allocation');
            $inputs     = [
                'sizeable_id'   => $allocation['id'],
                'sizeable_type' => Allocation::class,
            ];
        } else {
            $unload = $request->validated('selected_unload');
            $inputs = [
                'sizeable_id'   => $unload['id'],
                'sizeable_type' => Unload::class,
            ];
        }

        $sizing = Sizing::find($id);
        $sizing->update(array_merge($request->validated(), $inputs));
        $sizing->save();

        $inputs = $request->safe()->only(Sizing::CATEGORY_INPUTS);
        CategoriesHelper::createRelationOfTypes($inputs, $sizing->id, Sizing::class);

        NotificationHelper::updatedAction('Sizing', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRelations($id, Sizing::class);
        
        Sizing::destroy($id);

        NotificationHelper::deleteAction('Sizing', $id);

        return back();
    }

    private function getSizings($buyerId, $search = '')
    {
        return Sizing::query()
            ->with([
                'categories.category',
                'sizeable' => function (MorphTo $morphTo) {
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
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    return $subQuery
                        ->where('no_of_crew', 'LIKE', "%{$search}%")
                        ->orWhere('comments', 'LIKE', "%{$search}%")
                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%")
                        ->orWhereHasMorph(
                            'sizeable',
                            [Allocation::class, Unload::class],
                            function (Builder $query, string $type) use ($search) {
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
                            }
                        );
                });
            })
            ->where('user_id', $buyerId)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->onEachSide(1);
    }

    private static function getListOfModelUsers($search = '')
    {
        return Sizing::query()
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
            ->sortBy(fn($item) => $item->user->buyer_name ?? $item->user->grower_name)
            ->map(function ($item) {
                $item->id = $item->user_id;

                return $item;
            });
    }
}
