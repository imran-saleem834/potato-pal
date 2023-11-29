<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\{Receival, TiaSample};
use App\Http\Requests\TiaSampleRequest;
use Illuminate\Support\Facades\Storage;

class TiaSampleController extends Controller
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

        return Inertia::render('TiaSample/Index', [
            'receivals' => $receivals
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword    = $request->input('keyword', '');
        $tiaSamples = TiaSample::query()
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
                    ->orWhere('processor', 'LIKE', "%$keyword%")
                    ->orWhere('inspection_no', 'LIKE', "%$keyword%")
                    ->orWhere('inspection_date', 'LIKE', "%$keyword%")
                    ->orWhere('cool_store', 'LIKE', "%$keyword%")
                    ->orWhere('size', 'LIKE', "%$keyword%")
                    ->orWhere('status', 'LIKE', "%$keyword%");
            })
            ->get();

        $tiaSampleId = $request->input('tiaSampleId', $tiaSamples->first()->id ?? 0);

        $tiaSample = TiaSample::with(['receival.user', 'receival.categories.category'])->find($tiaSampleId);

        return response()->json([
            'tiaSamples' => $tiaSamples,
            'tiaSample'  => $tiaSample,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TiaSampleRequest $request)
    {
        TiaSample::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tiaSample = TiaSample::with(['receival.user', 'receival.categories.category'])->find($id);

        return response()->json($tiaSample);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TiaSampleRequest $request, string $id)
    {
        $tiaSample = TiaSample::find($id);
        $tiaSample->update($request->validated());
        $tiaSample->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        TiaSample::destroy($id);
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
        $fileName = $file->storePublicly('tia-samples');

        $tiaSample         = TiaSample::find($id);
        $images            = $tiaSample->images ?? [];
        $images[]          = $fileName;
        $tiaSample->images = $images;
        $tiaSample->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, string $id)
    {
        $fileName = $request->input('image');

        $tiaSample = TiaSample::find($id);
        $images    = $tiaSample->images ?? [];

        $pos = array_search($fileName, $images);
        if ($pos !== false) {
            unset($images[$pos]);

            Storage::disk()->delete($fileName);
        }

        $tiaSample->images = array_values($images);

        $tiaSample->save();
    }
}
