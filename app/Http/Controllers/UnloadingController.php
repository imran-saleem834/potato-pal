<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Unload;
use App\Models\Receival;
use Illuminate\Http\Request;
use App\Http\Requests\UnloadRequest;

class UnloadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Unload/Index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $unloads = Unload::query()
            ->with([
                'receival'      => function ($query) {
                    return $query->select('id', 'user_id');
                },
                'receival.user' => function ($query) {
                    return $query->select('id', 'name');
                }
            ])
            ->select('id', 'receival_id')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('receival_id', 'LIKE', "%$keyword%")
                    ->orWhere('total_seed_bins', 'LIKE', "%$keyword%")
                    ->orWhere('weight_seed_bins', 'LIKE', "%$keyword%")
                    ->orWhere('total_oversize_bins', 'LIKE', "%$keyword%")
                    ->orWhere('weight_oversize_bins', 'LIKE', "%$keyword%");
            })
            ->get();

        $unloadId = $request->input('unloadId', $unloads->first()->id ?? 0);

        $unload = Unload::with(['receival.user', 'receival.categories.category'])->find($unloadId);

        return response()->json([
            'unloads' => $unloads,
            'unload'  => $unload,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnloadRequest $request)
    {
        Unload::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unload = Unload::with(['receival.user'])->find($id);

        return response()->json($unload);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnloadRequest $request, string $id)
    {
        $unload = Unload::find($id);
        $unload->update($request->validated());
        $unload->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Receival::where('unload_id', $id)->update(['unload_id' => $id]);

        Unload::destroy($id);
    }
}
