<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Unload;
use App\Models\Category;
use App\Models\Receival;
use App\Models\TiaSample;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\ReceivalRequest;
use Illuminate\Database\Eloquent\Builder;

class ReceivalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $receivals = Receival::query()
            ->with(['grower' => fn ($query) => $query->select('id', 'name', 'grower_name')])
            ->select('id', 'grower_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->search($search);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $receivalId = $request->input('receivalId', $receivals->items()[0]->id ?? 0);

        return Inertia::render('Receival/Index', [
            'receivals'  => $receivals,
            'single'     => $this->getReceival($receivalId),
            'users'      => User::query()
                ->with(['categories.category'])
                ->select(['id', 'name', 'grower_name', 'paddocks'])
                ->whereJsonContains('role', 'grower')
                ->get(),
            'categories' => Category::whereIn('type', Receival::CATEGORY_TYPES)->get(),
            'filters'    => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReceivalRequest $request)
    {
        $inputs = $request->validated();

        $receival = Receival::create($inputs);

        if (! empty($inputs['created_at'])) {
            $receival->created_at = str_replace('T', ' ', $inputs['created_at']).':00';
            $receival->save();
        }

        $inputs = $request->only([
            'grower_group',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'transport',
        ]);
        CategoriesHelper::createRelationOfTypes($inputs, $receival->id, Receival::class);

        NotificationHelper::addedAction('Receival', $receival->id);

        return to_route('receivals.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receival = $this->getReceival($id);

        return response()->json($receival);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReceivalRequest $request, string $id)
    {
        $inputs = $request->validated();

        $receival = Receival::find($id);
        $receival->update($inputs);
        $receival->save();

        if (! empty($inputs['created_at'])) {
            $receival->created_at = str_replace('T', ' ', $inputs['created_at']).':00';
            $receival->save();
        }

        $inputs = $request->only([
            'grower_group',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'transport',
        ]);
        CategoriesHelper::createRelationOfTypes($inputs, $receival->id, Receival::class);

        ReceivalHelper::updateUniqueKey($receival);

        ReceivalHelper::calculateRemainingReceivals($receival->grower_id);

        NotificationHelper::updatedAction('Receival', $id);

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRelations($id, Receival::class);

        $receival = Receival::find($id);
        $growerId = $receival->grower_id;
        $receival->delete();

        Unload::where('receival_id', $id)->delete();

        ReceivalHelper::calculateRemainingReceivals($growerId);

        NotificationHelper::deleteAction('Receival', $id);

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function pushForUnload(string $id)
    {
        $receival         = Receival::find($id);
        $receival->status = 'pending';
        $receival->save();

        $inputs = ['fungicide' => ['Magnate/Vorlon']];
        CategoriesHelper::createRelationOfTypes($inputs, $receival->id, Receival::class);

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function pushForTiaSample(Request $request, string $id)
    {
        $status = $request->input('status');
        if ($status === 'applied') {
            $status = 'pending';
            TiaSample::firstOrCreate(['receival_id' => $id]);
        }

        Receival::find($id)->update(['tia_status' => $status]);

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function duplicate(Request $request, string $id)
    {
        $receival = Receival::with(['categories.category'])->find($id);

        $inputs     = [];
        $categories = [];
        $types      = [
            'grower_group',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'transport',
        ];
        foreach ($request->input('inputs') as $field => $isTrue) {
            if ($isTrue && $receival[$field]) {
                $inputs[$field] = $receival[$field];
            } elseif ($isTrue && in_array($field, $types)) {
                $field              = str_replace('_', '-', $field);
                $categories[$field] = $receival->categories->where('type', $field)->pluck('category_id')->toArray();
            }
        }

        $receival = Receival::create($inputs);

        CategoriesHelper::createRelationOfTypes($categories, $receival->id, Receival::class);

        NotificationHelper::duplicatedAction('Receival', $receival->id);

        return to_route('receivals.index');
    }

    private function getReceival($receivalId)
    {
        return Receival::with([
            'categories.category',
            'grower'    => fn ($query) => $query->select(['id', 'name', 'grower_name']),
            'tiaSample' => fn ($query) => $query->select(['id', 'receival_id']),
            'grower.categories.category',
        ])->find($receivalId);
    }
}
