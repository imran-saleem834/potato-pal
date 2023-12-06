<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Helpers\ReceivalHelper;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use App\Http\Requests\ReceivalRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\{User, Unload, Receival, TiaSample};

class ReceivalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Receival/Index', [
            'users' => User::select(['id', 'name', 'paddocks'])->get()->toArray()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword   = $request->input('keyword', '');
        $receivals = Receival::query()
            ->with([
                'grower' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->select('id', 'grower_id')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('paddocks', 'LIKE', "%$keyword%")
                    ->orWhere('grower_docket_no', 'LIKE', "%$keyword%")
                    ->orWhere('chc_receival_docket_no', 'LIKE', "%$keyword%")
                    ->orWhere('driver_name', 'LIKE', "%$keyword%")
                    ->orWhere('comments', 'LIKE', "%$keyword%");
            })
            ->latest()
            ->get();

        $receivalId = $request->input('receivalId', $receivals->first()->id ?? 0);

        $receival = Receival::with([
            'categories.category',
            'unload'    => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'tiaSample' => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'grower'    => function ($query) {
                return $query->select('id', 'name');
            },
        ])->find($receivalId);

        return response()->json([
            'receivals' => $receivals,
            'receival'  => $receival,
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

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receival = Receival::with([
            'categories.category',
            'unload'    => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'tiaSample' => function ($query) {
                return $query->select('id', 'receival_id');
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

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRealtions($id, Receival::class);
        Receival::destroy($id);

        NotificationHelper::deleteAction('Receival', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function pushForUnload(string $id)
    {
        Unload::firstOrCreate(['receival_id' => $id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function pushForTiaSample(string $id)
    {
        TiaSample::firstOrCreate(['receival_id' => $id]);
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
