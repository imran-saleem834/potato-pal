<?php

namespace App\Http\Controllers;

use App\Models\File;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\FileRequest;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('File/Index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $files   = File::query()
            ->select('id', 'title')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('title', 'LIKE', "%$keyword%");
            })
            ->latest()
            ->get();

        $fileId = $request->input('fileId', $files->first()->id ?? 0);

        $file = File::find($fileId);

        return response()->json([
            'files' => $files,
            'file'  => $file,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileRequest $request)
    {
        $file = File::create($request->validated());

        NotificationHelper::addedAction('File', $file->id);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $file = File::find($id);

        return response()->json($file);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FileRequest $request, string $id)
    {
        $file = File::find($id);
        $file->update($request->validated());
        $file->save();

        NotificationHelper::updatedAction('File', $id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        File::destroy($id);

        NotificationHelper::deleteAction('File', $id);
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
        $fileName = $file->storePublicly('files');

        $file         = File::find($id);
        $images       = $file->images ?? [];
        $images[]     = $fileName;
        $file->images = $images;
        $file->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, string $id)
    {
        $fileName = $request->input('image');

        $file   = File::find($id);
        $images = $file->images ?? [];

        $pos = array_search($fileName, $images);
        if ($pos !== false) {
            unset($images[$pos]);

            Storage::disk()->delete($fileName);
        }

        $file->images = array_values($images);

        $file->save();
    }
}
