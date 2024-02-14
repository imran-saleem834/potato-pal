<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\ReceivalRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{Category, Unload, User, Receival, TiaSample};

class ReceivalController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
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

        $inputs = $request->only([
            'grower_group',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'transport'
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

        $inputs = $request->only([
            'grower_group',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'transport'
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

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function pushForTiaSample(string $id)
    {
        TiaSample::firstOrCreate(['receival_id' => $id]);

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
            'transport'
        ];
        foreach ($request->input('inputs') as $field => $isTrue) {
            if ($isTrue && $receival[$field]) {
                $inputs[$field] = $receival[$field];
            } else if ($isTrue && in_array($field, $types)) {
                $field              = str_replace('_', '-', $field);
                $categories[$field] = $receival->categories->where('type', $field)->pluck('category_id')->toArray();
            }
        }

        $receival = Receival::create($inputs);

        CategoriesHelper::createRelationOfTypes($categories, $receival->id, Receival::class);

        NotificationHelper::duplicatedAction('Receival', $receival->id);

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function upload(Request $request, string $id)
    {
        $request->validate([
            'file' => ['required', 'mimes:jpeg,png,jpg,gif,svg,pdf', 'max:2048'],
        ]);

        $file     = $request->file('file');
        $fileName = $file->storePublicly('receivals');

        $receival         = Receival::find($id);
        $images           = $receival->images ?? [];
        $images[]         = $fileName;
        $receival->images = $images;
        $receival->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, string $id)
    {
        $fileName = $request->input('image');

        $receival = Receival::find($id);
        $images   = $receival->images ?? [];

        $pos = array_search($fileName, $images);
        if ($pos !== false) {
            unset($images[$pos]);

            Storage::disk()->delete($fileName);
        }

        $receival->images = array_values($images);

        $receival->save();
    }

    private function getReceival($receivalId)
    {
        return Receival::with([
            'categories.category',
            'grower'    => fn($query) => $query->select(['id', 'name', 'grower_name']),
            'tiaSample' => fn($query) => $query->select(['id', 'status', 'receival_id']),
            'grower.categories.category',
        ])->find($receivalId);
    }
}
