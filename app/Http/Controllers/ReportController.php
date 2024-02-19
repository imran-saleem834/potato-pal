<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use App\Models\Report;
use App\Models\Receival;
use App\Models\Category;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Report/Dashboard');
    }

    /**
     * @param Request $request
     * Display a listing of the resource.
     */
    public function show(Request $request, $type)
    {
        $reports = Report::query()
            ->where('type', $type)
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString()
            ->onEachSide(1);

        return Inertia::render('Report/Index', [
            'reports' => $reports,
            'name'    => Report::TYPES[$type] ?? 'Report',
            'type'    => $type,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request)
    {
        $type = $request->input('type');
        return Inertia::render('Report/New', [
            'name'       => Report::TYPES[$type] ?? 'Report',
            'type'       => $type,
            'categories' => Category::get(),
            'growers'    => User::query()
                ->select(['id', 'grower_name', 'paddocks'])
                ->whereJsonContains('role', 'grower')
                ->get(),
            'buyers'     => User::query()
                ->select(['id', 'buyer_name'])
                ->whereJsonContains('role', 'buyer')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $report = Report::create($request->all());

        NotificationHelper::addedAction('Report', $report->id);

        return to_route('reports.show', $report->type);
    }

    public function result(Request $request, Report $report)
    {
        $report = $this->getReport($report);

        return Inertia::render('Report/Result', [
            'report'  => $report,
            'name'    => Report::TYPES[$report->type] ?? 'Report',
            'type'    => $report->type,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function edit(Request $request, $id)
    {
        $report = Report::find($id);
        
        return Inertia::render('Report/Edit', [
            'report'     => $report,
            'name'       => Report::TYPES[$report->type] ?? 'Report',
            'type'       => $report->type,
            'categories' => Category::get(),
            'growers'    => User::query()
                ->select(['id', 'grower_name', 'paddocks'])
                ->whereJsonContains('role', 'grower')
                ->get(),
            'buyers'     => User::query()
                ->select(['id', 'buyer_name'])
                ->whereJsonContains('role', 'buyer')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = Report::find($id);
        $report->update($request->all());

        NotificationHelper::updatedAction('Report', $id);

        return to_route('reports.show', ['report' => $report->type, 'reportId' => $report->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CategoriesHelper::deleteCategoryRelations($id, Allocation::class);

        $report = Report::find($id);
        $type   = $report->type;
        $report->delete();

        NotificationHelper::deleteAction('Report', $id);

        return to_route('reports.show', $type);
    }

    private function getReport($report)
    {
        if ($report->getAttribute('type') === 'receival') {
            $report->setAttribute('data', $this->getFilterReceivals($report));
        }

        return $report;
    }

    private function getFilterReceivals($report)
    {
        $categoryIds = array_merge(
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
            $report->filters['seed_classes'] ?? [],
            // $report->filters['cool_stores'] ?? [],
            $report->filters['fungicides'] ?? [],
            $report->filters['transports'] ?? [],
        );

        return Receival::query()
            ->with(['grower:id,grower_name', 'categories.category', 'grower.categories.category'])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereIn('grower_id', $growerIds);
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->where(function (Builder $subQuery) use ($paddocks) {
                    foreach ($paddocks as $paddock) {
                        $subQuery->orWhereJsonContains('paddocks', $paddock);
                    }
                    return $subQuery;
                });
            })
            ->when($categoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }
}
