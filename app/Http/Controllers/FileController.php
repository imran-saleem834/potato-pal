<?php

namespace App\Http\Controllers;

use App\Models\File;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\FileRequest;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class FileController extends Controller
{
    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $files = File::query()
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%");
            })
            ->latest()
            ->get()
            ->groupBy(function ($file) {
                return $file->created_at->format('Y-m-d');
            });

        return Inertia::render('File/Index', [
            'files'   => $files,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileRequest $request)
    {
        $file     = $request->file('image');
        $fileName = $file->storePublicly('files');

        $file = File::create(array_merge($request->only('title'), ['image' => $fileName]));

        NotificationHelper::addedAction('File', $file->id);

        return to_route('files.index');
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
        $fileName = null;
        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = $file->storePublicly('files');
        }

        $file = File::find($id);
        $file->update($request->only('title'));
        $file->save();

        if ($file->image && $fileName) {
            try {
                Storage::disk()->delete($file->image);
            } catch (\Exception $e) {
                // Silence is golden
            }
        }

        if ($fileName) {
            $file->image = $fileName;
            $file->save();
        }

        NotificationHelper::updatedAction('File', $id);

        return to_route('files.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $file = File::find($id);

        if ($file->image) {
            try {
                Storage::disk()->delete($file->image);
            } catch (\Exception $e) {
                // Silence is golden
            }
        }

        $file->delete();

        NotificationHelper::deleteAction('File', $id);

        return to_route('files.index');
    }
}
