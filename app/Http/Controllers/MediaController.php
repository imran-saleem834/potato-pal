<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Note;
use App\Models\Receival;
use App\Models\TiaSample;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class MediaController extends Controller
{
    public function upload(Request $request, string $model, string $id)
    {
        $request->validate([
            'file' => ['required', 'mimes:jpeg,png,jpg,gif,svg,pdf', 'max:2048'],
        ]);

        $file     = $request->file('file');
        $fileName = $file->storePublicly($model);
        $field    = $request->input('field');

        $model = $this->getModel($model, $id);
        if (! $model) {
            return back();
        }

        $images        = $model[$field] ?? [];
        $images[]      = $fileName;
        $model[$field] = $images;
        $model->save();

        return back();
    }

    public function delete(Request $request, string $model, string $id)
    {
        $fileName = $request->input('image');
        $field    = $request->input('field');

        $model = $this->getModel($model, $id);
        if (! $model) {
            return back();
        }

        $images = $model[$field] ?? [];

        $pos = array_search($fileName, $images);
        if ($pos !== false) {
            unset($images[$pos]);

            Storage::disk()->delete($fileName);
        }

        $model[$field] = array_values($images);

        $model->save();

        return back();
    }

    public function files(Request $request)
    {
        $files = File::query()
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->where('id', 'LIKE', "%$search%")
                        ->orWhere('title', 'LIKE', "%$search%");
                });
            })
            ->latest()
            ->paginate(1)
            ->withQueryString()
            ->onEachSide(1);

        return [
            'files'   => $files,
            'filters' => $request->only(['search']),
        ];
    }

    public function attach(Request $request, string $modelName, string $id)
    {
        $request->validate([
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'string'],
        ]);

        $model = $this->getModel($modelName, $id);
        if (! $model) {
            return response()->json(['error' => 'unable to locate the modal']);
        }

        $fileImages = $request->input('images');
        $field      = $request->input('field');

        $images = $model[$field] ?? [];
        foreach ($fileImages as $image) {
            $fileName = explode('/', $image);
            $fileName = last($fileName);
            $fileName = Str::random().$fileName;
            $fileName = "$modelName/$fileName";
            Storage::disk('public')->copy($image, $fileName);

            $images[] = $fileName;
        }

        $model[$field] = $images;
        $model->save();

        return back();
    }

    private function getModel($model, $id): ?Model
    {
        if ($model === 'receivals') {
            return Receival::select(['id', 'images'])->find($id);
        } elseif ($model === 'tia-samples') {
            return TiaSample::select(['id', 'images'])->find($id);
        } elseif ($model === 'notes') {
            return Note::find($id);
        }

        return null;
    }
}
