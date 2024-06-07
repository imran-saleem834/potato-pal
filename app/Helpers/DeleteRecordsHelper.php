<?php

namespace App\Helpers;

class DeleteRecordsHelper
{
    public static function deleteAllocation($allocation)
    {
        // Delete allocation returns and dispatch items
        $allocation->returnItems()->delete();
        foreach ($allocation->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete();
            $dispatchItem->delete();
        }

        // Delete cuttings
        foreach ($allocation->cuttingItems as $cuttingItem) {
            $cutting = $cuttingItem->allocatable;
            static::deleteCutting($cutting);            // Delete Cutting
        }

        $allocation->item()->delete();
        $allocation->delete();
    }

    public static function deleteCutting($cutting)
    {
        $cutting->returnItems()->delete();
        foreach ($cutting->reallocationItems as $reallocationItem) {
            $reallocation = $reallocationItem->allocatable;
            static::deleteReallocation($reallocation);  // Delete Reallocation
        }
        foreach ($cutting->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete();     // Delete Dispatch
            $dispatchItem->delete();                    // Delete Dispatch Item
        }
        $cutting->item()->delete();
        $cutting->delete();
    }

    public static function deleteReallocation($reallocation)
    {
        $reallocation->returnItems()->delete();
        foreach ($reallocation->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete(); // Delete Dispatch
            $dispatchItem->delete();                // Delete Dispatch Item
        }
        $reallocation->item()->delete();
        $reallocation->delete();
    }
}
