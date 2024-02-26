<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Cutting;
use App\Models\CuttingAllocation;
use App\Models\Label;
use Inertia\Inertia;
use App\Models\Grade;
use App\Models\Unload;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Http\Requests\LabelRequest;
use Illuminate\Database\Eloquent\Builder;

class LabelController extends Controller
{
    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $labels = Label::query()
            ->with(['labelable'])
             ->when($request->input('search'), function (Builder $query, $search) {
            //     return $query->where(function (Builder $subQuery) use ($search) {
            //         return $subQuery->search($search);
            //     });
             })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        $labelId = $request->input('labelId', $labels->items()[0]->id ?? 0);

        return Inertia::render('Label/Index', [
            'labels'      => $labels,
            'single'      => $this->getGrade($labelId),
            'allocations' => $this->getAllocations(),
            'cuttings'    => $this->getCuttings(),
            'filters'     => $request->only(['search']),
        ]);
    }

    private function getAllocations()
    {
        return Allocation::query()
            ->with([
                'buyer' => fn($query) => $query->select(['id', 'buyer_name']),
                'grower' => fn($query) => $query->select(['id', 'grower_name']),
            ])
            ->get();
    }

    private function getCuttings()
    {
        return CuttingAllocation::query()
            ->with([
                'allocation'        => fn($query) => $query->select(['id', 'buyer_id', 'grower_id', 'bin_size', 'paddock']),
                'allocation.buyer'  => fn($query) => $query->select(['id', 'buyer_name']),
                'allocation.grower' => fn($query) => $query->select(['id', 'grower_name']),
            ])
            ->get();
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
    public function store(LabelRequest $request)
    {
        $grade = Label::create($request->validated());

        NotificationHelper::addedAction('Grade', $grade->id);

        return to_route('gradings.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabelRequest $request, string $id)
    {
        $grade = Label::find($id);
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
            'unload'                 => fn($query) => $query->select(['id', 'receival_id']),
            'unload.receival'        => fn($query) => $query->select(['id', 'grower_id']),
            'unload.receival.grower' => fn($query) => $query->select(['id', 'grower_name']),
        ])->find($gradeId);
    }
}
