<?php

namespace App\Helpers;

use App\Models\SizingItem;
use App\Models\User;
use App\Models\Unload;
use App\Models\Cutting;
use App\Models\Receival;
use App\Models\TiaSample;
use App\Models\Allocation;
use App\Models\Weighbridge;
use App\Models\Reallocation;
use App\Models\RemainingReceival;

// Todo: Delete Grading, Bulk Bagging, Sizing, Chemical Application
class DeleteRecordsHelper
{
    public static function deleteUnload($unload)
    {
        Weighbridge::where('unload_id', $unload->id)->delete();

        CategoriesHelper::deleteCategoryRelations($unload->id, Unload::class);

        $unload->delete();
    }

    public static function deleteReceival($receival)
    {
        $receival->loadMissing('unloads');
        foreach ($receival->unloads as $unload) {
            static::deleteUnload($unload);
        }

        TiaSample::where('receival_id', $receival->id)->delete();

        CategoriesHelper::deleteCategoryRelations($receival->id, Receival::class);

        $receival->delete();
    }

    public static function deleteAllocation($allocation)
    {
        // Delete allocation returns and dispatch items
        static::deleteReturnItems($allocation);
        $allocation->loadMissing('dispatchItems');
        foreach ($allocation->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete();
            $dispatchItem->delete();
        }

        // Delete cuttings
        $allocation->loadMissing('cuttingItems.allocatable');
        foreach ($allocation->cuttingItems as $cuttingItem) {
            $cutting = $cuttingItem->allocatable;
            static::deleteCutting($cutting);
        }

        CategoriesHelper::deleteCategoryRelations($allocation->id, Allocation::class);

        $allocation->item()->delete();
        $allocation->delete();
    }

    public static function deleteSizing($sizing)
    {
        foreach ($sizing->items as $item) {

            // Delete returns and dispatch items
            static::deleteReturnItems($item);
            $item->loadMissing('dispatchItems');
            foreach ($item->dispatchItems as $dispatchItem) {
                $dispatchItem->allocatable()->delete();
                $dispatchItem->delete();
            }
            
            // Delete Cutting
            foreach ($item->cuttingItems as $cuttingItem) {
                $cutting = $cuttingItem->allocatable;
                static::deleteCutting($cutting);
            }

            CategoriesHelper::deleteCategoryRelations($item->id, SizingItem::class);

            $item->delete();
        }
        
        $sizing->delete();
    }

    public static function deleteCutting($cutting)
    {
        static::deleteReturnItems($cutting);
        $cutting->loadMissing('reallocationItems.allocatable');
        foreach ($cutting->reallocationItems as $reallocationItem) {
            $reallocation = $reallocationItem->allocatable;
            static::deleteReallocation($reallocation);  // Delete Reallocation
        }

        $cutting->loadMissing('dispatchItems');
        foreach ($cutting->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete();     // Delete Dispatch
            $dispatchItem->delete();                    // Delete Dispatch Item
        }

        CategoriesHelper::deleteCategoryRelations($cutting->id, Cutting::class);

        $cutting->item()->delete();
        $cutting->delete();
    }

    public static function deleteReallocation($reallocation)
    {
        static::deleteReturnItems($reallocation);
        $reallocation->loadMissing('dispatchItems');
        foreach ($reallocation->dispatchItems as $dispatchItem) {
            $dispatchItem->allocatable()->delete(); // Delete Dispatch
            $dispatchItem->delete();                // Delete Dispatch Item
        }
        $reallocation->item()->delete();
        $reallocation->delete();
    }

    public static function deleteReturnItems($model)
    {
        $model->loadMissing('returnItems');

        foreach ($model->returnItems as $returnItem) {
            $returnItem->returns()->delete();
            $returnItem->delete();
        }
    }

    public static function deleteUser($id)
    {
        $user = User::with(['receivals.unloads'])->find($id);

        foreach ($user->receivals as $receival) {
            static::deleteReceival($receival);
        }

        RemainingReceival::where('grower_id', $user->id)->delete();

        $allocations = Allocation::query()
            ->where(function ($query) use ($user) {
                return $query
                    ->where('buyer_id', $user->id)
                    ->orWhere('grower_id', $user->id);
            })->get();
        foreach ($allocations as $allocation) {
            static::deleteAllocation($allocation);
        }

        $reallocations = Reallocation::query()
            ->where(function ($query) use ($user) {
                return $query
                    ->where('buyer_id', $user->id)
                    ->orWhere('allocation_buyer_id', $user->id);
            })->get();
        foreach ($reallocations as $reallocation) {
            static::deleteReallocation($reallocation);
        }

        CategoriesHelper::deleteCategoryRelations($user->id, User::class);

        $user->delete();
    }
}
