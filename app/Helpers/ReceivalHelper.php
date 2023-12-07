<?php

namespace App\Helpers;

use App\Models\Receival;
use App\Models\Allocation;
use App\Models\RemainingReceival;

class ReceivalHelper
{
    public static function getUniqueKey(Receival $receival): string
    {
        $growerGroupId = $seedTypeId = $seedVarietyId = $seedClassId = $seedGenerationId = '';
        $paddock = $receival->paddocks[0] ?? '';
        foreach ($receival->categories as $category) {
            if ($category->type === 'grower') {
                $growerGroupId = $category->category->id;
            }
            if ($category->type === 'seed-type') {
                $seedTypeId = $category->category->id;
            }
            if ($category->type === 'seed-variety') {
                $seedVarietyId = $category->category->id;
            }
            if ($category->type === 'seed-class') {
                $seedClassId = $category->category->id;
            }
            if ($category->type === 'seed-generation') {
                $seedGenerationId = $category->category->id;
            }
        }

        return "$growerGroupId-$seedTypeId-$seedVarietyId-$seedClassId-$seedGenerationId-$paddock";
    }

    public static function getCategoryNames(Receival $receival): array
    {
        $growerGroup = $seedType = $seedVariety = $seedClass = $seedGeneration = '';
        $paddock     = $receival->paddocks[0] ?? '';
        foreach ($receival->categories as $category) {
            if ($category->type === 'grower') {
                $growerGroup = $category->category->name;
            }
            if ($category->type === 'seed-type') {
                $seedType = $category->category->name;
            }
            if ($category->type === 'seed-variety') {
                $seedVariety = $category->category->name;
            }
            if ($category->type === 'seed-class') {
                $seedClass = $category->category->name;
            }
            if ($category->type === 'seed-generation') {
                $seedGeneration = $category->category->name;
            }
        }

        return [$paddock, $growerGroup, $seedType, $seedVariety, $seedClass, $seedGeneration];
    }

    public static function calculateRemainingReceivals(int $growerId)
    {
        $receivals = Receival::where('grower_id', $growerId)->get();

        RemainingReceival::where('grower_id', $growerId)->update(['oversize_bin_size' => 0, 'seed_bin_size' => 0]);

        foreach ($receivals as $receival) {

            $remainingReceival = RemainingReceival::firstOrCreate([
                'grower_id'  => $receival->grower_id,
                'unique_key' => $receival->unique_key,
            ]);

            $receivalIds   = $remainingReceival->receival_id ?? [];
            $receivalIds[] = $receival->id;

            $remainingReceival->receival_id       = array_values(array_unique($receivalIds));
            $remainingReceival->oversize_bin_size += $receival->oversize_bin_size;
            $remainingReceival->seed_bin_size     += $receival->seed_bin_size;

            $remainingReceival->save();
        }

        foreach ($receivals->keyBy('unique_key') as $receival) {

            $remainingReceival = RemainingReceival::firstOrCreate([
                'grower_id'  => $receival->grower_id,
                'unique_key' => $receival->unique_key,
            ]);

            $allocations = Allocation::query()
                ->where('grower_id', $receival->grower_id)
                ->where('unique_key', $receival->unique_key)
                ->get();

            foreach ($allocations as $allocation) {
                if ($allocation->allocated_type === 'seed') {
                    $remainingReceival->seed_bin_size -= $allocation->allocated_tonnes;
                } else if ($allocation->allocated_type === 'oversize') {
                    $remainingReceival->oversize_bin_size -= $allocation->allocated_tonnes;
                }
            }

            $remainingReceival->save();
        }
    }
}
