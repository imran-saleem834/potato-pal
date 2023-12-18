<?php

namespace App\Helpers;

use App\Models\Receival;
use App\Models\Allocation;
use App\Models\RemainingReceival;

class ReceivalHelper
{
    public static function getUniqueKey(Receival $receival): string|null
    {
        $uniqueKey = [];
        foreach ($receival->categories as $category) {
            if ($category->type === 'grower') {
                $uniqueKey[] = $category->category->id;
            }
            if ($category->type === 'seed-type') {
                $uniqueKey[] = $category->category->id;
            }
            if ($category->type === 'seed-variety') {
                $uniqueKey[] = $category->category->id;
            }
            if ($category->type === 'seed-class') {
                $uniqueKey[] = $category->category->id;
            }
            if ($category->type === 'seed-generation') {
                $uniqueKey[] = $category->category->id;
            }
            if ($category->type === 'seed-generation') {
                $uniqueKey[] = $category->category->id;
            }
        }

        if (!empty($receival->bin_size)) {
            $uniqueKey[] = $receival->bin_size;
        }

        if (isset($receival->paddocks[0]) && !empty($receival->paddocks[0])) {
            $uniqueKey[] = $receival->paddocks[0];
        }

        return empty($uniqueKey) ? null : implode('-', $uniqueKey);
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

        return [$receival->bin_size, $paddock, $growerGroup, $seedType, $seedVariety, $seedClass, $seedGeneration];
    }

    public static function calculateRemainingReceivals(int $growerId)
    {
        $receivals = Receival::where('grower_id', $growerId)->get();

        RemainingReceival::where('grower_id', $growerId)->update([
            'no_of_bins'  => 0,
            'weight'      => 0,
            'receival_id' => [],
        ]);

        foreach ($receivals as $receival) {
            if (!$receival->unique_key) {
                continue;
            }

            $remainingReceival = RemainingReceival::firstOrCreate([
                'grower_id'  => $receival->grower_id,
                'unique_key' => $receival->unique_key,
            ]);

            $receivalIds                    = $remainingReceival->receival_id ?? [];
            $receivalIds[]                  = $receival->id;
            $remainingReceival->receival_id = array_values(array_unique($receivalIds));
            $remainingReceival->no_of_bins  += $receival->no_of_bins;
            $remainingReceival->weight      += $receival->weight;

            $remainingReceival->save();
        }

        foreach ($receivals->keyBy('unique_key') as $receival) {
            if (!$receival->unique_key) {
                continue;
            }

            $remainingReceival = RemainingReceival::query()
                ->where('grower_id', $receival->grower_id)
                ->where('unique_key', $receival->unique_key)
                ->first();

            if (!$remainingReceival) {
                continue;
            }

            $allocations = Allocation::query()
                ->where('grower_id', $receival->grower_id)
                ->where('unique_key', $receival->unique_key)
                ->get();

            foreach ($allocations as $allocation) {
                $remainingReceival->no_of_bins  -= $allocation->no_of_bins;
                $remainingReceival->weight      -= $allocation->weight;
            }

            $remainingReceival->save();
        }
    }
}
