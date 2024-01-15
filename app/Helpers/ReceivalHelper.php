<?php

namespace App\Helpers;

use App\Models\Unload;
use App\Models\Receival;
use App\Models\Allocation;
use App\Models\RemainingReceival;

class ReceivalHelper
{
    public static function updateUniqueKey(Receival $receival): void
    {
        $receival->loadMissing('unloads');

        foreach ($receival->unloads as $unload) {
            $unload->unique_key = static::getUniqueKey($unload);
            $unload->save();
        }
    }

    public static function getUniqueKey(Unload $unload): string|null
    {
        $uniqueKey = [];
        foreach ($unload->receival->categories as $category) {
            if ($category->type === 'grower-group') {
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
        }
        unset($category);

        foreach ($unload->categories as $category) {
            if ($category->type === 'seed-type') {
                $uniqueKey[] = $category->category->id;
            }
        }

        if (!empty($unload->bin_size)) {
            $uniqueKey[] = $unload->bin_size;
        }

        if (isset($unload->receival->paddocks[0]) && !empty($unload->receival->paddocks[0])) {
            $uniqueKey[] = $unload->receival->paddocks[0];
        }

        return empty($uniqueKey) ? null : implode('-', $uniqueKey);
    }

    public static function calculateRemainingReceivals(int $growerId)
    {
        $receivals = Receival::with(['unloads'])->where('grower_id', $growerId)->get();

        RemainingReceival::where('grower_id', $growerId)->update([
            'no_of_bins'  => 0,
            'weight'      => 0,
            'receival_id' => [],
            'unload_id'   => [],
        ]);

        foreach ($receivals as $receival) {
            if ($receival->unloads->isEmpty()) {
                continue;
            }
            if ($receival->status !== 'completed') {
                continue;
            }

            foreach ($receival->unloads as $unload) {
                if (empty($unload->unique_key)) {
                    continue;
                }

                $remainingReceival = RemainingReceival::firstOrCreate([
                    'grower_id'  => $receival->grower_id,
                    'unique_key' => $unload->unique_key,
                ]);

                $receivalIds                    = $remainingReceival->receival_id ?? [];
                $receivalIds[]                  = $receival->id;
                $remainingReceival->receival_id = array_values(array_unique($receivalIds));

                $unloadIds                    = $remainingReceival->unload_id ?? [];
                $unloadIds[]                  = $unload->id;
                $remainingReceival->unload_id = array_values(array_unique($unloadIds));

                $remainingReceival->no_of_bins += $unload->no_of_bins;
                $remainingReceival->weight     += $unload->weight;

                $remainingReceival->save();
            }
        }

        foreach ($receivals as $receival) {
            if ($receival->unloads->isEmpty()) {
                continue;
            }
            if ($receival->status !== 'completed') {
                continue;
            }

            foreach ($receival->unloads->keyBy('unique_key') as $unload) {
                if (empty($unload->unique_key)) {
                    continue;
                }

                $remainingReceival = RemainingReceival::query()
                    ->where('grower_id', $receival->grower_id)
                    ->where('unique_key', $unload->unique_key)
                    ->first();

                if (!$remainingReceival) {
                    continue;
                }

                $allocations = Allocation::query()
                    ->where('grower_id', $receival->grower_id)
                    ->where('unique_key', $unload->unique_key)
                    ->get();

                foreach ($allocations as $allocation) {
                    $remainingReceival->no_of_bins -= $allocation->no_of_bins;
                    $remainingReceival->weight     -= $allocation->weight;
                }

                $remainingReceival->save();
            }
        }
        
        RemainingReceival::query()
            ->where('grower_id', $growerId)
            ->where('receival_id', [])
            ->where('unload_id', [])
            ->where('weight', 0)
            ->where('no_of_bins', 0)
            ->delete();
    }
}
