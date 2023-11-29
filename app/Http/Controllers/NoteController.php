<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\NoteRequest;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $notes   = Note::query()
            ->select('id', 'title')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('title', 'LIKE', "%$keyword%")
                    ->orWhere('note', 'LIKE', "%$keyword%")
                    ->orWhere('tags', 'LIKE', "%$keyword%")
                    ->orWhere('images', 'LIKE', "%$keyword%");
            })
            ->get();

        $noteId = $request->input('noteId', $notes->first()->id ?? 0);

        $note = Note::find($noteId);

        return Inertia::render('Note/Index', [
            'notes'   => $notes,
            'note'    => $note,
            'keyword' => $request->input('keyword'),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $notes   = Note::query()
            ->select('id', 'title')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('id', 'LIKE', "%$keyword%")
                    ->orWhere('title', 'LIKE', "%$keyword%")
                    ->orWhere('note', 'LIKE', "%$keyword%")
                    ->orWhere('tags', 'LIKE', "%$keyword%")
                    ->orWhere('images', 'LIKE', "%$keyword%");
            })
            ->get();

        $noteId = $request->input('noteId', $notes->first()->id ?? 0);

        $note = Note::find($noteId);

        return response()->json([
            'notes' => $notes,
            'note'  => $note,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteRequest $request)
    {
        Note::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = Note::find($id);

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteRequest $request, string $id)
    {
        $note = Note::find($id);
        $note->update($request->validated());
        $note->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Note::destroy($id);
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
        $fileName = $file->storePublicly('notes');

        $note         = Note::find($id);
        $images       = $note->images ?? [];
        $images[]     = $fileName;
        $note->images = $images;
        $note->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, string $id)
    {
        $fileName = $request->input('image');

        $note   = Note::find($id);
        $images = $note->images ?? [];

        $pos = array_search($fileName, $images);
        if ($pos !== false) {
            unset($images[$pos]);

            Storage::disk()->delete($fileName);
        }

        $note->images = array_values($images);

        $note->save();
    }
}
