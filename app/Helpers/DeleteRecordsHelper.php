<?php

namespace App\Helpers;

use App\Models\Cutting;
use App\Models\Allocation;
use App\Models\AllocationItem;

class DeleteRecordsHelper
{
    public static function deleteAllocation($allocation)
    {
        // Delete cutting
        static::deleteCuttingByAllocationId($allocation->id);

        // Delete allocation returns and dispatch items
        $allocation->returns()->delete();
        foreach ($allocation->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete();
            $dispatchItem->delete();
        }

        // Delete reallocation returns and dispatch items
        foreach ($allocation->reallocationItems as $reallocationItem) {
            $reallocation = $reallocationItem->allocatable;
            static::deleteReallocation($reallocation);
        }

        $allocation->item()->delete();
        $allocation->delete();
    }

    public static function deleteReallocation($reallocation)
    {
        $reallocation->returns()->delete();
        foreach ($reallocation->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete();
            $dispatchItem->delete();
        }
        $reallocation->item()->delete();
        $reallocation->delete();
    }

    private static function deleteCuttingByAllocationId($id)
    {
        $cuttingIds             = [];
        $cuttingAllocationItems = AllocationItem::query()
            ->where('allocatable_type', Cutting::class)
            ->where('foreignable_type', Allocation::class)
            ->where('foreignable_id', $id)
            ->get();
        foreach ($cuttingAllocationItems as $cuttingAllocationItem) {
            $cuttingIds[] = $cuttingAllocationItem->allocatable_id;
            $cuttingAllocationItem->delete();
        }

        // Remove Cutting if all it's allocations are removed
        $cuttingIds = array_unique($cuttingIds);
        foreach ($cuttingIds as $cuttingId) {
            $isMoreCuttingAllocationExists = AllocationItem::query()
                ->where('allocatable_type', Cutting::class)
                ->where('allocatable_id', $cuttingId)
                ->exists();
            if (! $isMoreCuttingAllocationExists) {
                Cutting::destroy($cuttingId);
            }
        }
    }
}
