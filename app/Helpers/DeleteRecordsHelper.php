<?php

namespace App\Helpers;

use App\Models\Cutting;
use App\Models\Allocation;
use App\Models\CuttingAllocation;
use App\Models\Dispatch;
use App\Models\Reallocation;

class DeleteRecordsHelper
{
    public static function deleteAllocation($id)
    {
        Allocation::destroy($id);

        static::deleteReallocationsByAllocationId($id);
        static::deleteDisaptachesByAllocationId($id);
        static::deleteCuttingByAllocationId($id);
    }

    private static function deleteReallocationsByAllocationId($id)
    {
        $reallocations = Reallocation::where('allocation_id', $id)->get();
        
        foreach ($reallocations as $reallocation) {
            static::deleteDisaptachByReallocationId($reallocation->id);
        }

        Reallocation::where('allocation_id', $id)->delete();
    }

    private static function deleteDisaptachesByAllocationId($id)
    {
        Dispatch::where('allocation_id', $id)->delete();
    }

    public static function deleteDisaptachByReallocationId($id)
    {
        Dispatch::where('reallocation_id', $id)->delete();
    }

    private static function deleteCuttingByAllocationId($id)
    {
        $cuttingIds         = [];
        $cuttingAllocations = CuttingAllocation::where('allocation_id', $id)->get();
        foreach ($cuttingAllocations as $cuttingAllocation) {
            $cuttingIds[] = $cuttingAllocation->cutting_id;
            $cuttingAllocation->delete();
        }

        // Remove Cutting if all it's allocations are removed
        $cuttingIds = array_unique($cuttingIds);
        foreach ($cuttingIds as $cuttingId) {
            $isMoreCuttingAllocationExists = CuttingAllocation::where('cutting_id', $cuttingId)->exists();
            if (!$isMoreCuttingAllocationExists) {
                Cutting::destroy($cuttingId);
            }
        }
    }
}
