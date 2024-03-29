<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\NoteRequest;
use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Builder;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $notes = Note::query()
            ->select('id', 'title', 'tags', 'created_at')
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->where('id', 'LIKE', "%$search%")
                        ->orWhere('title', 'LIKE', "%$search%")
                        ->orWhere('note', 'LIKE', "%$search%")
                        ->orWhere('tags', 'LIKE', "%$search%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $noteId = $request->input('noteId', $notes->items()[0]->id ?? 0);

        return Inertia::render('Note/Index', [
            'notes'   => $notes,
            'single'  => Note::find($noteId),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteRequest $request)
    {
        $note = Note::create($request->validated());

        NotificationHelper::addedAction('Note', $note->id);

        return to_route('notes.index');
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

        NotificationHelper::updatedAction('Note', $id);

        return to_route('notes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Note::destroy($id);

        NotificationHelper::deleteAction('Note', $id);

        return to_route('notes.index');
    }
}
