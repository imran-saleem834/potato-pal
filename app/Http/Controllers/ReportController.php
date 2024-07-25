<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Label;
use App\Models\Report;
use App\Models\Unload;
use App\Models\Grading;
use App\Models\Category;
use App\Models\Dispatch;
use App\Models\Receival;
use App\Models\TiaSample;
use App\Models\Allocation;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Models\CuttingAllocation;
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
     * @param  Request  $request
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
        } elseif ($report->getAttribute('type') === 'unload') {
            $report->setAttribute('data', $this->getFilterUnloads($report));
        } elseif ($report->getAttribute('type') === 'tia-sample') {
            $report->setAttribute('data', $this->getFilterTiaSamples($report));
        } elseif ($report->getAttribute('type') === 'allocation') {
            $report->setAttribute('data', $this->getFilterAllocations($report));
        } elseif ($report->getAttribute('type') === 'reallocation') {
            $report->setAttribute('data', $this->getFilterReallocations($report));
        } elseif ($report->getAttribute('type') === 'label') {
            $report->setAttribute('data', $this->getFilterLabels($report));
        } elseif ($report->getAttribute('type') === 'grade') {
            $report->setAttribute('data', $this->getFilterGradings($report));
        } elseif ($report->getAttribute('type') === 'cutting') {
            $report->setAttribute('data', $this->getFilterCuttings($report));
        } elseif ($report->getAttribute('type') === 'dispatch') {
            $report->setAttribute('data', $this->getFilterDispatch($report));
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
            $report->filters['transports'] ?? [],
            $report->filters['delivery_types'] ?? [],
        );

        return Receival::query()
            ->with(['grower:id,grower_name', 'categories.category'])
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

    private function getFilterUnloads($report)
    {
        $receivalCategoryIds = array_merge(
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
            $report->filters['fungicides'] ?? [],
        );
        $unloadCategoryIds   = $report->filters['seed_types'] ?? [];

        return Unload::query()
            ->with([
                'categories.category',
                'receival:id,grower_id,paddocks',
                'receival.grower:id,grower_name',
                'receival.categories.category',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('receival', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($report->filters['channels'] ?? null, function (Builder $query, $channels) {
                return $query->whereIn('channel', $channels);
            })
            ->when($report->filters['bin_sizes'] ?? null, function (Builder $query, $bin_sizes) {
                return $query->whereIn('bin_size', $bin_sizes);
            })
            ->when($report->filters['systems'] ?? null, function (Builder $query, $systems) {
                return $query->whereIn('system', $systems);
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereRelation('receival', function (Builder $subQuery) use ($paddocks) {
                    return $subQuery->where(function (Builder $paddockQuery) use ($paddocks) {
                        foreach ($paddocks as $paddock) {
                            $paddockQuery->orWhereJsonContains('paddocks', $paddock);
                        }

                        return $paddockQuery;
                    });
                });
            })
            ->when($unloadCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->when($receivalCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('receival.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterTiaSamples($report)
    {
        $receivalCategoryIds = array_merge(
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );

        return TiaSample::query()
            ->with([
                'receival:id,grower_id,grower_docket_no',
                'receival.grower:id,grower_name',
                'receival.categories.category',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('receival', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($report->filters['size'] ?? null, function (Builder $query, $size) {
                return $query->whereIn('size', $size);
            })
            ->when($receivalCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('receival.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterAllocations($report)
    {
        $categoryIds = array_merge(
            $report->filters['seed_types'] ?? [],
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );

        return Allocation::query()
            ->with([
                'buyer:id,buyer_name',
                'grower:id,grower_name',
                'categories.category',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereIn('grower_id', $growerIds);
            })
            ->when($report->filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($report->filters['bin_sizes'] ?? null, function (Builder $query, $binSizes) {
                return $query->whereIn('bin_size', $binSizes);
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereIn('paddock', $paddocks);
            })
            ->when($categoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterReallocations($report)
    {
        $categoryIds = array_merge(
            $report->filters['seed_types'] ?? [],
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );

        return Reallocation::query()
            ->with([
                'buyer:id,buyer_name',
                'allocationBuyer:id,buyer_name',
                'allocation:id,grower_id,bin_size,paddock',
                'allocation.grower:id,grower_name',
                'allocation.categories.category',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($report->filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($report->filters['allocation_buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('allocation_buyer_id', $buyerIds);
            })
            ->when($report->filters['bin_sizes'] ?? null, function (Builder $query, $binSizes) {
                return $query->whereIn('bin_size', $binSizes);
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($paddocks) {
                    return $subQuery->whereIn('paddock', $paddocks);
                });
            })
            ->when($categoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('allocation.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterLabels($report)
    {
        $categoryIds = array_merge(
            $report->filters['seed_types'] ?? [],
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );

        return Label::query()
            ->with([
                'buyer:id,buyer_name',
                'grower:id,grower_name',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['labelable_type'] ?? null, function (Builder $query, $types) {
                return $query->whereIn('labelable_type', $types);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('grower_id', $buyerIds);
            })
            ->when($report->filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereIn('paddock', $paddocks);
            })
            ->when($categoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('receivals.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterGradings($report)
    {
        $categoryIds = array_merge(
            $report->filters['seed_types'] ?? [],
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );

        return Grading::query()
//            ->with([
//                'buyer:id,buyer_name',
//                'grower:id,grower_name',
//            ])
//            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
//                return $query->where('created_at', '>=', $start);
//            })
//            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
//                return $query->where('created_at', '<=', $end);
//            })
//            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $buyerIds) {
//                return $query->whereIn('grower_id', $buyerIds);
//            })
//            ->when($report->filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
//                return $query->whereIn('buyer_id', $buyerIds);
//            })
//            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
//                return $query->whereIn('paddock', $paddocks);
//            })
//            ->when($categoryIds, function (Builder $query, $categoryIds) {
//                return $query->whereRelation('receivals.categories', function (Builder $subQuery) use ($categoryIds) {
//                    return $subQuery->whereIn('category_id', $categoryIds);
//                });
//            })
            ->get();
    }

    private function getFilterCuttings($report)
    {
        $allocationCategoryIds = array_merge(
            $report->filters['seed_types'] ?? [],
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );
        $cuttingCategoryIds    = $report->filters['fungicides'] ?? [];

        return CuttingAllocation::query()
            ->with([
                'allocation:id,buyer_id,grower_id,bin_size,paddock',
                'allocation.buyer:id,buyer_name',
                'allocation.grower:id,grower_name',
                'cutting.categories.category',
                'allocation.categories.category',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($report->filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereRelation('cutting', function (Builder $subQuery) use ($buyerIds) {
                    return $subQuery->whereIn('buyer_id', $buyerIds);
                });
            })
            ->when($report->filters['bin_sizes'] ?? null, function (Builder $query, $binSizes) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($binSizes) {
                    return $subQuery->whereIn('bin_size', $binSizes);
                });
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($paddocks) {
                    return $subQuery->whereIn('paddock', $paddocks);
                });
            })
            ->when($cuttingCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('cutting.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('allocation.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterDispatch($report)
    {
        $allocationCategoryIds = array_merge(
            $report->filters['seed_types'] ?? [],
            $report->filters['grower_groups'] ?? [],
            $report->filters['seed_varieties'] ?? [],
            $report->filters['seed_generations'] ?? [],
        );

        return Dispatch::query()
            ->with([
                'buyer:id,buyer_name',
                'allocation:id,buyer_id,grower_id,bin_size,paddock',
                'reallocation.allocation:id,buyer_id,grower_id,bin_size,paddock',
                'allocation.grower:id,grower_name',
                'reallocation.allocation.grower:id,grower_name',
                'allocation.buyer:id,buyer_name',
                'reallocation.buyer:id,buyer_name',
                'allocation.categories.category',
                'reallocation.allocation.categories.category',
            ])
            ->when($report->filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($report->filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($report->filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                })->orWhereRelation('reallocation.allocation', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($report->filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereRelation('cutting', function (Builder $subQuery) use ($buyerIds) {
                    return $subQuery->whereIn('buyer_id', $buyerIds);
                });
            })
            ->when($report->filters['bin_sizes'] ?? null, function (Builder $query, $binSizes) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($binSizes) {
                    return $subQuery->whereIn('bin_size', $binSizes);
                })->orWhereRelation('reallocation.allocation', function (Builder $subQuery) use ($binSizes) {
                    return $subQuery->whereIn('bin_size', $binSizes);
                });
            })
            ->when($report->filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereRelation('allocation', function (Builder $subQuery) use ($paddocks) {
                    return $subQuery->whereIn('paddock', $paddocks);
                })->orWhereRelation('reallocation.allocation', function (Builder $subQuery) use ($paddocks) {
                    return $subQuery->whereIn('paddock', $paddocks);
                });
            })
            ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('allocation.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                })->orWhereRelation('reallocation.allocation.categories',
                    function (Builder $subQuery) use ($categoryIds) {
                        return $subQuery->whereIn('category_id', $categoryIds);
                    });
            })
            ->get();
    }
}
