<?php

namespace App\Helpers;

use App\Models\Allocation;
use App\Models\Reallocation;
use Illuminate\Support\Arr;

class AllocationHelper
{
    public static function getAvailableAllocationForCutting(array $filter, ?array $with = [])
    {
        return Allocation::query()
            ->with(array_merge(['item', 'returns', 'cuttingItems', 'dispatchItems'], $with))
            ->when($filter['buyer_id'] ?? null, function ($query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));   
            })
            ->when($filter['id'] ?? null, function ($query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($allocation) {
                // Set available bins
                $binsInKg = $allocation->item->no_of_bins * $allocation->item->bin_size;

                // Remove dispatch bins
                foreach ($allocation->dispatchItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                // Set return bins
                foreach ($allocation->returns as $item) {
                    $binsInKg += $item->no_of_bins * $item->bin_size;
                }

                // Remove already cut bins
                foreach ($allocation->cuttingItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                $allocation->total_no_of_bins     = $allocation->item->no_of_bins;
                $allocation->available_no_of_bins = $binsInKg / $allocation->item->bin_size;

                return $allocation;
            });
    }
    
    public static function getAvailableAllocationForReallocation(array $filter, ?array $with = [])
    {
        return Allocation::query()
            ->has('cuttingItems')
            ->with(array_merge(['item', 'returns', 'cuttingItems', 'reallocationItems', 'dispatchItems'], $with))
            ->when($filter['buyer_id'] ?? null, function ($query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));   
            })
            ->when($filter['allocation_id'] ?? null, function ($query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($allocation) {
                // Set available bins
                $binsInKg = $allocation->cuttingItems->sum('no_of_bins') * $allocation->item->bin_size;

                // Remove dispatch bins
                foreach ($allocation->dispatchItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                // Set return bins
                foreach ($allocation->returns as $item) {
                    $binsInKg += $item->no_of_bins * $item->bin_size;
                }

                // Remove already cut bins
                foreach ($allocation->reallocationItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                $allocation->total_no_of_bins     = $allocation->cuttingItems->sum('no_of_bins');
                $allocation->available_no_of_bins = $binsInKg / $allocation->item->bin_size;

                return $allocation;
            });
    }
    
    public static function getAvailableAllocationForDispatch(array $filter, ?array $with = [])
    {
        $allocations = Allocation::query()
            ->with(array_merge(['item', 'returns', 'reallocationItems', 'dispatchItems', 'buyer:id,buyer_name'], $with))
            ->when($filter['buyer_id'] ?? null, function ($query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['allocation_id'] ?? null, function ($query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($allocation) {
                // Set available bins
                $binsInKg = $allocation->item->no_of_bins * $allocation->item->bin_size;

                // Remove reallocation bins
                foreach ($allocation->reallocationItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                // Remove dispatch bins
                foreach ($allocation->dispatchItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                // Add returns bins
                foreach ($allocation->returns as $item) {
                    $binsInKg += $item->no_of_bins * $item->bin_size;
                }

                $allocation->type                 = 'allocation';
                $allocation->total_no_of_bins     = $allocation->item->no_of_bins;
                $allocation->available_no_of_bins = $binsInKg / $allocation->item->bin_size;

                return $allocation;
            });

        $with = Arr::map($with, fn ($w) => 'item.foreignable.' . $w);
        $with[] = 'item.foreignable.item.foreignable';

        $reallocations = Reallocation::query()
            ->with(array_merge(['item', 'returns', 'dispatchItems', 'buyer:id,buyer_name'], $with))
            ->when($filter['buyer_id'] ?? null, function ($query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['reallocation_id'] ?? null, function ($query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->each(function ($reallocation) {
                // Set available bins
                $binsInKg = $reallocation->item->no_of_bins * $reallocation->item->bin_size;

                // Remove dispatch bins
                foreach ($reallocation->dispatchItems as $item) {
                    $binsInKg -= $item->no_of_bins * $item->bin_size;
                }

                // Add returns bins
                foreach ($reallocation->returns as $item) {
                    $binsInKg += $item->no_of_bins * $item->bin_size;
                }

                $reallocation->type                 = 'reallocation';
                $reallocation->total_no_of_bins     = $reallocation->item->no_of_bins;
                $reallocation->available_no_of_bins = $binsInKg / $reallocation->item->bin_size;

                return $reallocation;
            });

        return $allocations->concat($reallocations);
    }
}
