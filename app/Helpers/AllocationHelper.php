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
    public static function getAvailableAllocations(array $filter, ?array $with = [])
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
            ->doesntHave('sizing')
            ->get()
            ->map(function ($model) {
                $model = static::setAvailableBins($model);
                $model = static::removeCuttingBins($model);
                return static::removeDispatchAndSetReturnBins($model);
            });
    }

    public static function getAvailableCuttings(array $filter, ?array $with = [])
    {
        return Cutting::query()
            ->with(array_merge(['item.foreignable', 'reallocationItems', 'dispatchItems', 'returnItems'], $with))
            ->when($filter['buyer_id'] ?? null, function (Builder $query, $buyerId) {
                return $query->whereIn('buyer_id', Arr::wrap($buyerId));
            })
            ->when($filter['id'] ?? null, function (Builder $query, $id) {
                return $query->whereIn('id', Arr::wrap($id));
            })
            ->get()
            ->map(function ($model) {
                $model = self::setAvailableBins($model);
                $model = self::setAvailableFromBins($model);
                $model = self::removeReallocationBins($model);
                return static::removeDispatchAndSetReturnBins($model);
            });
    }

    public static function getAvailableSizing(array $filter, ?array $with = [])
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
                $model = static::removeCuttingBins($model);
                return static::removeDispatchAndSetReturnBins($model);
            });
    }

    public static function getAvailableReallocations(array $filter, ?array $with = [])
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
            ->map(function ($model) {
                $model = self::setAvailableBins($model);
                $model = self::setAvailableFromBins($model);
                return static::removeDispatchAndSetReturnBins($model);
            });
    }

    public static function setAvailableBins($model)
    {
        $model->available_half_tonnes = $model->item->half_tonnes;
        $model->available_one_tonnes  = $model->item->one_tonnes;
        $model->available_two_tonnes  = $model->item->two_tonnes;

        return $model;
    }

    public static function setAvailableFromBins($model)
    {
        $model->available_from_half_tonnes = $model->item->from_half_tonnes;
        $model->available_from_one_tonnes  = $model->item->from_one_tonnes;
        $model->available_from_two_tonnes  = $model->item->from_two_tonnes;

        return $model;
    }

    public static function removeCuttingBins($model)
    {
        foreach ($model->cuttingItems as $item) {
            $model->available_half_tonnes -= $item->from_half_tonnes;
            $model->available_one_tonnes  -= $item->from_one_tonnes;
            $model->available_two_tonnes  -= $item->from_two_tonnes;
        }

        return $model;
    }
    public static function removeReallocationBins($model)
    {
        foreach ($model->reallocationItems as $item) {
            $model->available_half_tonnes -= $item->half_tonnes;
            $model->available_one_tonnes  -= $item->one_tonnes;
            $model->available_two_tonnes  -= $item->two_tonnes;

            $model->available_from_half_tonnes -= $item->from_half_tonnes;
            $model->available_from_one_tonnes  -= $item->from_one_tonnes;
            $model->available_from_two_tonnes  -= $item->from_two_tonnes;
        }

        return $model;
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
