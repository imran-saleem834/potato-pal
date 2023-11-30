<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\{Unload, Receival};
use App\Helpers\NotificationHelper;
use App\Http\Requests\UnloadRequest;

class UnloadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $receivals = Receival::query()
            ->with([
                'user' => function ($query) {
                    return $query->select(['id', 'name']);
                }
            ])
            ->select(['id', 'user_id'])
            ->get()
            ->map(function ($receival) {
                return ['value' => $receival->id, 'label' => "ReceivalId:{$receival->id} {$receival->user->name}"];
            });

        return Inertia::render('Unload/Index', [
            'receivals' => $receivals
        ]);
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
            ->latest()
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
        $unload = Unload::create($request->validated());

        NotificationHelper::addedAction('Unload', $unload->id);

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

        NotificationHelper::updatedAction('Unload', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Unload::destroy($id);

        NotificationHelper::deleteAction('Unload', $id);
    }
}
