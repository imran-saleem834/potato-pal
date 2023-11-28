<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Http\Requests\ReceivalRequest;
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
        $keyword = $request->input('keyword', '');
        $receivals = Receival::query()
            ->with([
                'user' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->select('id', 'user_id')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('paddocks', 'LIKE', "%$keyword%")
                    ->orWhere('tia_sample_id', 'LIKE', "%$keyword%")
                    ->orWhere('unload_id', 'LIKE', "%$keyword%")
                    ->orWhere('grower_docket_no', 'LIKE', "%$keyword%")
                    ->orWhere('chc_receival_docket_no', 'LIKE', "%$keyword%")
                    ->orWhere('chc_receival_docket_no', 'LIKE', "%$keyword%")
                    ->orWhere('driver_name', 'LIKE', "%$keyword%")
                    ->orWhere('comments', 'LIKE', "%$keyword%");
            })
            ->get();

        $receivalId = $request->input('receivalId', $receivals->first()->id ?? 0);

        $receival = Receival::with([
            'categories',
            'unload' => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'tiaSample' => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'user' => function ($query) {
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

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receival = Receival::with([
            'categories',
            'unload' => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'tiaSample' => function ($query) {
                return $query->select('id', 'receival_id');
            },
            'user' => function ($query) {
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

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRealtions($id, Receival::class);
        Receival::destroy($id);
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
}
