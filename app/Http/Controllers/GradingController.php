<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Grade;
use App\Models\Unload;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\GradeRequest;
use Illuminate\Database\Eloquent\Builder;

class GradingController extends Controller
{
    /**
     * @param  Request  $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $grades = Grade::query()
            ->with([
                'unload'                 => fn ($query) => $query->select(['id', 'receival_id']),
                'unload.receival'        => fn ($query) => $query->select(['id', 'grower_id']),
                'unload.receival.grower' => fn ($query) => $query->select(['id', 'grower_name']),
            ])
            ->select(['id', 'unload_id'])
            ->when($request->input('search'), function (Builder $query, $search) {
                return $query->where(function (Builder $subQuery) use ($search) {
                    return $subQuery->search($search);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $gradeId = $request->input('gradeId', $grades->items()[0]->id ?? 0);

        return Inertia::render('Grade/Index', [
            'grades'     => $grades,
            'single'     => $this->getGrade($gradeId),
            'unloads'    => $this->getUnloads(),
            'categories' => Grade::CATEGORIES,
            'filters'    => $request->only(['search']),
        ]);
    }

    private function getUnloads()
    {
        return Unload::query()
            ->with([
                'categories.category',
                'receival:id,grower_id',
                'receival.grower:id,grower_name',
            ])
            ->select(['id', 'receival_id'])
            ->whereRelation('receival', 'status', '=', 'completed')
            ->get()
            ->map(function ($unload) {
                $categoryName = $unload->categories?->first()?->category?->name;
                $label        = '';
                if ($categoryName) {
                    $label = $categoryName;
                }

                return [
                    'value' => $unload->id,
                    'label' => "Unload id: {$unload->id}; $label; Receival id: {$unload->receival_id}; {$unload->receival->grower->grower_name}",
                ];
            });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grade = $this->getGrade($id);

        return response()->json($grade);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GradeRequest $request)
    {
        $grade = Grade::create($request->validated());

        NotificationHelper::addedAction('Grade', $grade->id);

        return to_route('gradings.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GradeRequest $request, string $id)
    {
        $grade = Grade::find($id);
        $grade->update($request->validated());
        $grade->save();

        NotificationHelper::updatedAction('Grade', $id);

        return to_route('gradings.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Grade::destroy($id);

        NotificationHelper::deleteAction('Grade', $id);

        return to_route('gradings.index');
    }

    private function getGrade($gradeId)
    {
        return Grade::with([
            'unload'                 => fn ($query) => $query->select(['id', 'receival_id']),
            'unload.receival'        => fn ($query) => $query->select(['id', 'grower_id']),
            'unload.receival.grower' => fn ($query) => $query->select(['id', 'grower_name']),
        ])->find($gradeId);
    }
}
