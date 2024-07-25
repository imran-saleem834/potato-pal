<?php

namespace App\Helpers;

use App\Models\Sizing;
use App\Models\Cutting;
use App\Models\Allocation;
use App\Models\SizingItem;
use Illuminate\Support\Arr;
use App\Models\Reallocation;
use Illuminate\Database\Eloquent\Builder;

class AllocationHelper
{
    public static function getAvailableAllocationForCutting(array $filter, ?array $with = [])
    {
        return Allocation::query()
            ->with(array_merge(['item', 'cuttingItems', 'dispatchItems', 'returnItems'], $with))
            ->when($filter['buyer_id'] ?? null, function (Builder $query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['id'] ?? null, function (Builder $query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($allocation) {
                // Set available bins
                $binsInKg = $allocation->item->no_of_bins * $allocation->item->bin_size;

                // Remove dispatch bins
                foreach ($allocation->dispatchItems as $item) {
                    $binsInKg -= $item->half_tonnes * 500;
                    $binsInKg -= $item->one_tonnes * 1000;
                    $binsInKg -= $item->two_tonnes * 2000;
                }

                // Set return bins
                foreach ($allocation->returnItems as $item) {
                    $binsInKg += $item->half_tonnes * 500;
                    $binsInKg += $item->one_tonnes * 1000;
                    $binsInKg += $item->two_tonnes * 2000;
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

    public static function getAvailableCuttingsForReallocation(array $filter, ?array $with = [])
    {
        return Cutting::query()
            ->with(array_merge(['item.foreignable', 'dispatchItems', 'returnItems', 'reallocationItems'], $with))
            ->when($filter['buyer_id'] ?? null, function (Builder $query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['id'] ?? null, function (Builder $query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($cutting) {
                return self::setAvailableForCutting($cutting);
            });
    }

    public static function getAvailableAllocationForDispatch(array $filter, ?array $with = [])
    {
        return Allocation::query()
            ->with(array_merge(['item', 'cuttingItems', 'dispatchItems', 'returnItems'], $with))
            ->withSum(['baggings'], 'no_of_bulk_bags_out')
            ->when($filter['buyer_id'] ?? null, function (Builder $query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['id'] ?? null, function (Builder $query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($allocation) {
                return static::setAvailableForAllocation($allocation);
            });
    }

    public static function getAvailableSizingForDispatch(array $filter, ?array $with = [])
    {
        return SizingItem::query()
            ->with(array_merge(['allocatable.sizeable', 'cuttingItems', 'dispatchItems', 'returnItems'], $with))
            ->where('allocatable_type', Sizing::class)
            ->whereHasMorph('allocatable', [Sizing::class], function (Builder $query) use ($filter) {
                return $query
                    ->where('sizeable_type', Allocation::class)
                    ->when($filter['user_id'] ?? null, function (Builder $query, $userId) {
                        return $query->whereIn('user_id', Arr::wrap($userId));
                    });
            })
            ->when($filter['id'] ?? null, function (Builder $query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($model) {
                $model->available_half_tonnes = $model->half_tonnes;
                $model->available_one_tonnes  = $model->one_tonnes;
                $model->available_two_tonnes  = $model->two_tonnes;

                // Remove cutting bins
                foreach ($model->cuttingItems as $item) {
                    $model->available_half_tonnes -= $item->half_tonnes;
                    $model->available_one_tonnes  -= $item->one_tonnes;
                    $model->available_two_tonnes  -= $item->two_tonnes;
                }

                return static::removeDispatchAndSetReturnBins($model);
            });
    }

    public static function getAvailableReallocationForDispatch(array $filter, ?array $with = [])
    {
        return Reallocation::query()
            ->with(array_merge(['item', 'returnItems', 'dispatchItems', 'buyer:id,buyer_name'], $with))
            ->when($filter['buyer_id'] ?? null, function (Builder $query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['id'] ?? null, function (Builder $query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($reallocation) {
                return self::setAvailableForReallocation($reallocation);
            });
    }

    public static function setAvailableForAllocation($model)
    {
        $model->available_half_tonnes = (int) $model->item->bin_size === 500 ? $model->item->no_of_bins : 0;
        $model->available_one_tonnes  = (int) $model->item->bin_size === 1000 ? $model->item->no_of_bins : 0;
        $model->available_two_tonnes  = (int) $model->item->bin_size === 2000 ? $model->item->no_of_bins : 0;

        // Remove cutting bins
        foreach ($model->cuttingItems as $item) {
            $model->available_half_tonnes -= (int) $item->bin_size === 500 ? $item->no_of_bins : 0;
            $model->available_one_tonnes  -= (int) $item->bin_size === 1000 ? $item->no_of_bins : 0;
            $model->available_two_tonnes  -= (int) $item->bin_size === 2000 ? $item->no_of_bins : 0;
        }

        return self::removeDispatchAndSetReturnBins($model);
    }

    public static function setAvailableForCutting($model)
    {
        $model = static::setAvailableForReallocation($model);

        // Remove reallocation bins
        foreach ($model->reallocationItems as $item) {
            $model->available_half_tonnes -= $item->half_tonnes;
            $model->available_one_tonnes  -= $item->one_tonnes;
            $model->available_two_tonnes  -= $item->two_tonnes;
        }

        return $model;
    }

    public static function setAvailableForReallocation($model)
    {
        $model->available_half_tonnes = $model->item->half_tonnes;
        $model->available_one_tonnes  = $model->item->one_tonnes;
        $model->available_two_tonnes  = $model->item->two_tonnes;

        return static::removeDispatchAndSetReturnBins($model);
    }

    public static function removeDispatchAndSetReturnBins($model)
    {
        // Remove dispatch bins
        foreach ($model->dispatchItems as $item) {
            $model->available_half_tonnes -= $item->half_tonnes;
            $model->available_one_tonnes  -= $item->one_tonnes;
            $model->available_two_tonnes  -= $item->two_tonnes;
        }

        // Add return bins
        foreach ($model->returnItems as $item) {
            $model->available_half_tonnes += $item->half_tonnes;
            $model->available_one_tonnes  += $item->one_tonnes;
            $model->available_two_tonnes  += $item->two_tonnes;
        }

        return $model;
    }
}
