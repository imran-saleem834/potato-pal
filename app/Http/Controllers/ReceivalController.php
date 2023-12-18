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
use App\Models\{Category, User, Receival, TiaSample};

class ReceivalController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     */
    public function index(Request $request)
    {
        $receivals = Receival::query()
            ->with([
                'grower' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->select('id', 'grower_id')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->search($search);
                });
            })
            ->latest()
            ->get();

        $receivalId = $request->input('receivalId', $receivals->first()->id ?? 0);

        $receival = Receival::with([
            'categories.category',
            'tiaSample' => function ($query) {
                return $query->select('id', 'receival_id', 'status');
            },
            'grower'    => function ($query) {
                return $query->select('id', 'name');
            },
        ])->find($receivalId);

        $types      = [
            'grower',
            'seed-type',
            'seed-variety',
            'seed-generation',
            'seed-class',
            'delivery-type',
            'fungicide',
            'transport'
        ];
        $categories = Category::whereIn('type', $types)->get();

        return Inertia::render('Receival/Index', [
            'receivals'  => $receivals,
            'single'     => $receival,
            'users'      => User::select(['id', 'name', 'paddocks'])->get()->toArray(),
            'categories' => $categories,
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
            'grower',
            'seed_type',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'fungicide',
            'transport'
        ]);
        CategoriesHelper::createRelationOfTypes($inputs, $receival->id, Receival::class);

        $receival->unique_key = ReceivalHelper::getUniqueKey($receival);
        $receival->save();

        ReceivalHelper::calculateRemainingReceivals($receival->grower_id);

        NotificationHelper::addedAction('Receival', $receival->id);

        return to_route('receivals.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receival = Receival::with([
            'categories.category',
            'tiaSample' => function ($query) {
                return $query->select('id', 'receival_id', 'status');
            },
            'grower'    => function ($query) {
                return $query->select('id', 'name');
            },
        ])->find($id);

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
            'grower',
            'seed_type',
            'seed_variety',
            'seed_generation',
            'seed_class',
            'delivery_type',
            'fungicide',
            'transport'
        ]);
        CategoriesHelper::createRelationOfTypes($inputs, $receival->id, Receival::class);

        $receival->unique_key = ReceivalHelper::getUniqueKey($receival);
        $receival->save();

        ReceivalHelper::calculateRemainingReceivals($receival->grower_id);

        NotificationHelper::updatedAction('Receival', $id);

        return to_route('receivals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRealtions($id, Receival::class);

        $receival = Receival::find($id);
        $growerId = $receival->grower_id;
        $receival->delete();

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
}
