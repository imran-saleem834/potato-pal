<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Label;
use App\Models\Sizing;
use App\Models\Report;
use App\Models\Unload;
use App\Models\Grading;
use App\Models\Cutting;
use App\Models\Category;
use App\Models\Dispatch;
use App\Models\Receival;
use App\Models\TiaSample;
use App\Models\Allocation;
use App\Models\SizingItem;
use App\Models\Reallocation;
use Illuminate\Http\Request;
use App\Helpers\CategoriesHelper;
use App\Helpers\NotificationHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

        return Inertia::render('Report/New', $this->getDataForCreateAndEditView($type));
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

        return Inertia::render('Report/Edit', array_merge(
            ['report' => $report],
            $this->getDataForCreateAndEditView($report->type)
        ));
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

    private function getDataForCreateAndEditView($type)
    {
        $buyers  = collect();
        $growers = collect();
        if (!in_array(auth()->user()->role, ['buyer', 'grower'])) {
            $buyers  = User::query()
                ->select(['id', 'buyer_name'])
                ->whereJsonContains('access', 'buyer')
                ->get();
            $growers = User::query()
                ->select(['id', 'grower_name', 'paddocks'])
                ->whereJsonContains('access', 'grower')
                ->get();
        }

        return [
            'name'       => Report::TYPES[$type] ?? 'Report',
            'type'       => $type,
            'categories' => Category::get(),
            'growers'    => $growers,
            'buyers'     => $buyers,
        ];
    }

    private function getReport($report)
    {
        $filters = $report->filters ?? [];
        if (auth()->user()->role === 'buyer') {
            $filters['buyer_ids'] = [auth()->id()];
        }
        if (auth()->user()->role === 'grower') {
            $filters['grower_ids'] = [auth()->id()];
        }
            
        if ($report->getAttribute('type') === 'user') {
            $report->setAttribute('data', $this->getFilterUsers($filters));
        } elseif ($report->getAttribute('type') === 'receival') {
            $report->setAttribute('data', $this->getFilterReceivals($filters));
        } elseif ($report->getAttribute('type') === 'unload') {
            $report->setAttribute('data', $this->getFilterUnloads($filters));
        } elseif ($report->getAttribute('type') === 'tia-sample') {
            $report->setAttribute('data', $this->getFilterTiaSamples($filters));
        } elseif ($report->getAttribute('type') === 'allocation') {
            $report->setAttribute('data', $this->getFilterAllocations($filters));
        } elseif ($report->getAttribute('type') === 'reallocation') {
            $report->setAttribute('data', $this->getFilterReallocations($filters));
        } elseif ($report->getAttribute('type') === 'label') {
            $report->setAttribute('data', $this->getFilterLabels($filters));
        } elseif ($report->getAttribute('type') === 'grade') {
            $report->setAttribute('data', $this->getFilterGradings($filters));
        } elseif ($report->getAttribute('type') === 'cutting') {
            $report->setAttribute('data', $this->getFilterCuttings($filters));
        } elseif ($report->getAttribute('type') === 'dispatch') {
            $report->setAttribute('data', $this->getFilterDispatch($filters));
        }

        return $report;
    }

    private function getFilterUsers($filters)
    {
        $categoryIds = array_merge(
            $filters['grower_groups'] ?? [],
            $filters['buyer_groups'] ?? [],
            $filters['cool_stores'] ?? [],
        );

        return User::query()
            ->with(['categories.category'])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->where(function (Builder $subQuery) use ($paddocks) {
                    foreach ($paddocks as $paddock) {
                        $subQuery->orWhere('paddocks', 'LIKE', "%{$paddock}%");
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

    private function getFilterReceivals($filters)
    {
        $categoryIds = array_merge(
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
            $filters['seed_classes'] ?? [],
            $filters['transports'] ?? [],
            $filters['delivery_types'] ?? [],
        );

        return Receival::query()
            ->with(['grower:id,grower_name', 'categories.category'])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereIn('grower_id', $growerIds);
            })
            ->when($filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
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

    private function getFilterUnloads($filters)
    {
        $receivalCategoryIds = array_merge(
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
            $filters['fungicides'] ?? [],
        );
        $unloadCategoryIds   = $filters['seed_types'] ?? [];

        return Unload::query()
            ->with([
                'categories.category',
                'receival:id,grower_id,paddocks',
                'receival.grower:id,grower_name',
                'receival.categories.category',
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('receival', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($filters['channels'] ?? null, function (Builder $query, $channels) {
                return $query->whereIn('channel', $channels);
            })
            ->when($filters['bin_sizes'] ?? null, function (Builder $query, $bin_sizes) {
                return $query->whereIn('bin_size', $bin_sizes);
            })
            ->when($filters['systems'] ?? null, function (Builder $query, $systems) {
                return $query->whereIn('system', $systems);
            })
            ->when($filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
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

    private function getFilterTiaSamples($filters)
    {
        $receivalCategoryIds = array_merge(
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
        );

        return TiaSample::query()
            ->with([
                'receival:id,grower_id,paddocks,grower_docket_no',
                'receival.categories.category',
                'receival.grower:id,grower_name',
                'receival.grower.categories.category',
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereRelation('receival', function (Builder $subQuery) use ($growerIds) {
                    return $subQuery->whereIn('grower_id', $growerIds);
                });
            })
            ->when($filters['size'] ?? null, function (Builder $query, $size) {
                return $query->whereIn('size', $size);
            })
            ->when($receivalCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('receival.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterAllocations($filters)
    {
        $categoryIds = array_merge(
            $filters['seed_types'] ?? [],
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
        );

        return Allocation::query()
            ->with([
                'item',
                'buyer:id,buyer_name',
                'grower:id,grower_name',
                'categories.category',
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['grower_ids'] ?? null, function (Builder $query, $growerIds) {
                return $query->whereIn('grower_id', $growerIds);
            })
            ->when($filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($filters['bin_sizes'] ?? null, function (Builder $query, $binSizes) {
                return $query->whereIn('bin_size', $binSizes);
            })
            ->when($filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereIn('paddock', $paddocks);
            })
            ->when($categoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterCuttings($filters)
    {
        $allocationCategoryIds = array_merge(
            $filters['seed_types'] ?? [],
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
        );
        $cuttingCategoryIds    = array_merge(
            $filters['fungicides'] ?? [],
            $filters['cool_stores'] ?? []
        );
        $allocationPaddocks   = $filters['paddocks'] ?? null;
        $allocationGrowerIds  = $filters['grower_ids'] ?? null;
        $isAllocationFiltered = $allocationCategoryIds || $allocationPaddocks || $allocationGrowerIds;

        return Cutting::query()
            ->with([
                'categories.category',
                'buyer:id,buyer_name',
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class   => [
                            'item',
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                        SizingItem::class   => [
                            'categories.category',
                            'allocatable.sizeable.item',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                    ]);
                },
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($cuttingCategoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->when($isAllocationFiltered, function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                return $query
                    ->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                        return $query
                            ->where('foreignable_type', Allocation::class)
                            ->whereHasMorph('foreignable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query
                                    ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                        return $query->whereIn('paddock', $paddocks);
                                    })
                                    ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                        return $query->whereIn('grower_id', $growerIds);
                                    })
                                    ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                        return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                            return $subQuery->whereIn('category_id', $categoryIds);
                                        });
                                    });
                            });
                    })
                    ->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                        return $query
                            ->where('foreignable_type', SizingItem::class)
                            ->whereHasMorph('foreignable', [SizingItem::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query->where(function (Builder $query) use ($allocationCategoryIds) {
                                    return $query->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                        return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                            return $subQuery->whereIn('category_id', $categoryIds);
                                        });
                                    });
                                })->orWhere(function (Builder $query)  use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                    return $query->where('allocatable_type', Sizing::class)
                                        ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                            return $query
                                                ->whereHasMorph('sizeable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                    return $query
                                                        ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                            return $query->whereIn('paddock', $paddocks);
                                                        })
                                                        ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                            return $query->whereIn('grower_id', $growerIds);
                                                        })
                                                        ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                            return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                return $subQuery->whereIn('category_id', $categoryIds);
                                                            });
                                                        });
                                                });
                                        });
                                });
                            });
                    });
            })
            ->get();
    }

    private function getFilterReallocations($filters)
    {
        $allocationCategoryIds = array_merge(
            $filters['seed_types'] ?? [],
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
        );

        $allocationPaddocks   = $filters['paddocks'] ?? null;
        $allocationGrowerIds  = $filters['grower_ids'] ?? null;
        $isAllocationFiltered = $allocationCategoryIds || $allocationPaddocks || $allocationGrowerIds;

        return Reallocation::query()
            ->with([
                'buyer:id,buyer_name',
                'allocationBuyer:id,buyer_name',
                'item.foreignable.item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Allocation::class => [
                            'item',
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                        SizingItem::class => [
                            'categories.category',
                            'allocatable.sizeable.item',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                    ]);
                },
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($filters['allocation_buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('allocation_buyer_id', $buyerIds);
            })
            ->when($isAllocationFiltered, function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                return $query->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                    return $query
                        ->where('foreignable_type', Cutting::class)
                        ->whereHasMorph('foreignable', [Cutting::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                            return $query->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query
                                    ->where('foreignable_type', Allocation::class)
                                    ->whereHasMorph('foreignable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                        return $query
                                            ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                return $query->whereIn('paddock', $paddocks);
                                            })
                                            ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                return $query->whereIn('grower_id', $growerIds);
                                            })
                                            ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                    return $subQuery->whereIn('category_id', $categoryIds);
                                                });
                                            });
                                    });
                            })->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query
                                    ->where('foreignable_type', SizingItem::class)
                                    ->whereHasMorph('foreignable', [SizingItem::class],function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                        return $query->where(function (Builder $query) use ($allocationCategoryIds) {
                                            return $query->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                    return $subQuery->whereIn('category_id', $categoryIds);
                                                });
                                            });
                                        })->orWhere(function (Builder $query)  use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                            return $query->where('allocatable_type', Sizing::class)
                                                ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                    return $query
                                                        ->whereHasMorph('sizeable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                            return $query
                                                                ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                                    return $query->whereIn('paddock', $paddocks);
                                                                })
                                                                ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                                    return $query->whereIn('grower_id', $growerIds);
                                                                })
                                                                ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                                    return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                        return $subQuery->whereIn('category_id', $categoryIds);
                                                                    });
                                                                });
                                                        });
                                                });
                                        });
                                    });
                            });
                        });
                });
            })
            ->get();
    }

    private function getFilterLabels($filters)
    {
        $categoryIds = array_merge(
            $filters['seed_types'] ?? [],
            $filters['grower_groups'] ?? [],
            $filters['seed_generations'] ?? [],
        );

        return Label::query()
            ->with([
                'buyer:id,buyer_name',
                'grower:id,grower_name',
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['labelable_type'] ?? null, function (Builder $query, $types) {
                return $query->whereIn('labelable_type', $types);
            })
            ->when($filters['grower_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('grower_id', $buyerIds);
            })
            ->when($filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
                return $query->whereIn('paddock', $paddocks);
            })
            ->when($categoryIds, function (Builder $query, $categoryIds) {
                return $query->whereRelation('receivals.categories', function (Builder $subQuery) use ($categoryIds) {
                    return $subQuery->whereIn('category_id', $categoryIds);
                });
            })
            ->get();
    }

    private function getFilterGradings($filters)
    {
        $categoryIds = array_merge(
            $filters['seed_types'] ?? [],
            $filters['grower_groups'] ?? [],
            $filters['seed_generations'] ?? [],
        );

        return Grading::query()
//            ->with([
//                'buyer:id,buyer_name',
//                'grower:id,grower_name',
//            ])
//            ->when($filters['start'] ?? null, function (Builder $query, $start) {
//                return $query->where('created_at', '>=', $start);
//            })
//            ->when($filters['end'] ?? null, function (Builder $query, $end) {
//                return $query->where('created_at', '<=', $end);
//            })
//            ->when($filters['grower_ids'] ?? null, function (Builder $query, $buyerIds) {
//                return $query->whereIn('grower_id', $buyerIds);
//            })
//            ->when($filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
//                return $query->whereIn('buyer_id', $buyerIds);
//            })
//            ->when($filters['paddocks'] ?? null, function (Builder $query, $paddocks) {
//                return $query->whereIn('paddock', $paddocks);
//            })
//            ->when($categoryIds, function (Builder $query, $categoryIds) {
//                return $query->whereRelation('receivals.categories', function (Builder $subQuery) use ($categoryIds) {
//                    return $subQuery->whereIn('category_id', $categoryIds);
//                });
//            })
            ->get();
    }

    private function getFilterDispatch($filters)
    {
        $allocationCategoryIds = array_merge(
            $filters['seed_types'] ?? [],
            $filters['grower_groups'] ?? [],
            $filters['seed_varieties'] ?? [],
            $filters['seed_generations'] ?? [],
        );

        $allocationPaddocks   = $filters['paddocks'] ?? null;
        $allocationGrowerIds  = $filters['grower_ids'] ?? null;
        $isAllocationFiltered = $allocationCategoryIds || $allocationPaddocks || $allocationGrowerIds;

        return Dispatch::query()
            ->with([
                'item',
                'buyer:id,buyer_name',
                'categories.category',
                'item.foreignable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Reallocation::class => [
                            'item.foreignable.item.foreignable' => function (MorphTo $morphTo) {
                                $morphTo->morphWith([
                                    Allocation::class => [
                                        'categories.category',
                                        'grower:id,grower_name',
                                    ],
                                    SizingItem::class => [
                                        'categories.category',
                                        'allocatable.sizeable.categories.category',
                                        'allocatable.sizeable.grower:id,grower_name',
                                    ],
                                ]);
                            },
                        ],
                        Cutting::class      => [
                            'item.foreignable' => function (MorphTo $morphTo) {
                                $morphTo->morphWith([
                                    Allocation::class => [
                                        'categories.category',
                                        'grower:id,grower_name',
                                    ],
                                    SizingItem::class => [
                                        'categories.category',
                                        'allocatable.sizeable.categories.category',
                                        'allocatable.sizeable.grower:id,grower_name',
                                    ],
                                ]);
                            },
                        ],
                        SizingItem::class   => [
                            'categories.category',
                            'allocatable.sizeable.categories.category',
                            'allocatable.sizeable.grower:id,grower_name',
                        ],
                        Allocation::class   => [
                            'categories.category',
                            'grower:id,grower_name',
                        ],
                    ]);
                },
            ])
            ->when($filters['start'] ?? null, function (Builder $query, $start) {
                return $query->where('created_at', '>=', $start);
            })
            ->when($filters['end'] ?? null, function (Builder $query, $end) {
                return $query->where('created_at', '<=', $end);
            })
            ->when($filters['buyer_ids'] ?? null, function (Builder $query, $buyerIds) {
                return $query->whereIn('buyer_id', $buyerIds);
            })
            ->when($isAllocationFiltered, function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                return $query
                    ->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                        return $query
                            ->where('foreignable_type', Allocation::class)
                            ->whereHasMorph('foreignable', [Allocation::class],function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query
                                    ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                        return $query->whereIn('paddock', $paddocks);
                                    })
                                    ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                        return $query->whereIn('grower_id', $growerIds);
                                    })
                                    ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                        return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                            return $subQuery->whereIn('category_id', $categoryIds);
                                        });
                                    });
                            });
                    })
                    ->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                        return $query
                            ->where('foreignable_type', SizingItem::class)
                            ->whereHasMorph('foreignable', [SizingItem::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query->where(function (Builder $query) use ($allocationCategoryIds) {
                                    return $query->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                        return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                            return $subQuery->whereIn('category_id', $categoryIds);
                                        });
                                    });
                                })->orWhere(function (Builder $query)  use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                    return $query->where('allocatable_type', Sizing::class)
                                        ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                            return $query
                                                ->whereHasMorph('sizeable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                    return $query
                                                        ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                            return $query->whereIn('paddock', $paddocks);
                                                        })
                                                        ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                            return $query->whereIn('grower_id', $growerIds);
                                                        })
                                                        ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                            return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                return $subQuery->whereIn('category_id', $categoryIds);
                                                            });
                                                        });
                                                });
                                        });
                                });
                            });
                    })
                    ->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                        return $query
                            ->where('foreignable_type', Cutting::class)
                            ->whereHasMorph('foreignable', [Cutting::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                    return $query
                                        ->where('foreignable_type', Allocation::class)
                                        ->whereHasMorph('foreignable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                            return $query
                                                ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                    return $query->whereIn('paddock', $paddocks);
                                                })
                                                ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                    return $query->whereIn('grower_id', $growerIds);
                                                })
                                                ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                    return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                        return $subQuery->whereIn('category_id', $categoryIds);
                                                    });
                                                });
                                        });
                                })->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                    return $query
                                        ->where('foreignable_type', SizingItem::class)
                                        ->whereHasMorph('foreignable', [SizingItem::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                            return $query->where(function (Builder $query) use ($allocationCategoryIds) {
                                                return $query->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                    return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                        return $subQuery->whereIn('category_id', $categoryIds);
                                                    });
                                                });
                                            })->orWhere(function (Builder $query)  use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                return $query->where('allocatable_type', Sizing::class)
                                                    ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                        return $query
                                                            ->whereHasMorph('sizeable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                                return $query
                                                                    ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                                        return $query->whereIn('paddock', $paddocks);
                                                                    })
                                                                    ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                                        return $query->whereIn('grower_id', $growerIds);
                                                                    })
                                                                    ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                                        return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                            return $subQuery->whereIn('category_id', $categoryIds);
                                                                        });
                                                                    });
                                                            });
                                                    });
                                            });
                                        });
                                });
                            });
                    })
                    ->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                        return $query
                            ->where('foreignable_type', Reallocation::class)
                            ->whereHasMorph('foreignable', [Reallocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                return $query->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                    return $query
                                        ->where('foreignable_type', Cutting::class)
                                        ->whereHasMorph('foreignable', [Cutting::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                return $query->whereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                    return $query
                                                        ->where('foreignable_type', Allocation::class)
                                                        ->whereHasMorph('foreignable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                            return $query
                                                                ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                                    return $query->whereIn('paddock', $paddocks);
                                                                })
                                                                ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                                    return $query->whereIn('grower_id', $growerIds);
                                                                })
                                                                ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                                    return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                        return $subQuery->whereIn('category_id', $categoryIds);
                                                                    });
                                                                });
                                                        });
                                                })->orWhereHas('item', function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                    return $query
                                                        ->where('foreignable_type', SizingItem::class)
                                                        ->whereHasMorph('foreignable', [SizingItem::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                            return $query->where(function (Builder $query) use ($allocationCategoryIds) {
                                                                return $query->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                                    return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                        return $subQuery->whereIn('category_id', $categoryIds);
                                                                    });
                                                                });
                                                            })->orWhere(function (Builder $query)  use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                                return $query->where('allocatable_type', Sizing::class)
                                                                    ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                                        return $query
                                                                            ->whereHasMorph('sizeable', [Allocation::class], function (Builder $query) use ($allocationCategoryIds, $allocationPaddocks, $allocationGrowerIds) {
                                                                                return $query
                                                                                    ->when($allocationPaddocks, function (Builder $query, $paddocks) {
                                                                                        return $query->whereIn('paddock', $paddocks);
                                                                                    })
                                                                                    ->when($allocationGrowerIds, function (Builder $query, $growerIds) {
                                                                                        return $query->whereIn('grower_id', $growerIds);
                                                                                    })
                                                                                    ->when($allocationCategoryIds, function (Builder $query, $categoryIds) {
                                                                                        return $query->whereRelation('categories', function (Builder $subQuery) use ($categoryIds) {
                                                                                            return $subQuery->whereIn('category_id', $categoryIds);
                                                                                        });
                                                                                    });
                                                                            });
                                                                    });
                                                            });
                                                        });
                                                });
                                        });
                                });
                            });
                    });
            })
            ->get();
    }
}
