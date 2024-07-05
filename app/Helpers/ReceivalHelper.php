<?php

namespace App\Helpers;

use App\Models\Unload;
use App\Models\Category;
use App\Models\Receival;
use App\Models\TiaSample;
use App\Models\Allocation;
use App\Models\RemainingReceival;
use App\Models\CategoriesRelation;
use Illuminate\Database\Eloquent\Builder;

class ReceivalHelper
{
    public static function updateUniqueKey(Receival $receival): void
    {
        $receival->loadMissing('unloads.receival.categories.category', 'unloads.categories.category');

        foreach ($receival->unloads as $unload) {
            $unload->unique_key = static::getUniqueKey($unload);
            $unload->save();
        }
    }

    public static function pushForTiaSample(int $receivalId): void
    {
        if (static::isSeedClassPending($receivalId)) {
            TiaSample::firstOrCreate(['receival_id' => $receivalId], ['size' => '70mm']);
        }
    }

    public static function isSeedClassPending(int $receivalId)
    {
        $category = Category::where('name', 'pending')->where('type', 'seed-class')->first();

        return CategoriesRelation::where([
            'category_id'        => $category->id,
            'categorizable_id'   => $receivalId,
            'categorizable_type' => Receival::class,
            'type'               => $category->type,
        ])->exists();
    }

    private static function getUniqueKey(Unload $unload): ?string
    {
        if ($unload->receival->status !== 'completed') {
            return null;
        }

        $seedTypeCategory = $unload->categories->firstWhere('type', 'seed-type')?->category;
        if ($seedTypeCategory?->name === 'Oversize') {
            $uniqueKey = array_values(array_filter([
                $unload->receival->categories->firstWhere('type', 'grower-group')?->category?->id,
                $unload->receival->categories->firstWhere('type', 'seed-variety')?->category?->id,
                $unload->receival->categories->firstWhere('type', 'seed-generation')?->category?->id,
                $seedTypeCategory?->id,
                $unload->bin_size,
                $unload->receival->paddocks[0] ?? null,
            ]));

            return count($uniqueKey) == 6 ? implode('-', $uniqueKey) : null;
        } else {
            $uniqueKey = array_values(array_filter([
                $unload->receival->categories->firstWhere('type', 'grower-group')?->category?->id,
                $unload->receival->categories->firstWhere('type', 'seed-variety')?->category?->id,
                $unload->receival->categories->firstWhere('type', 'seed-class')?->category?->id,
                $unload->receival->categories->firstWhere('type', 'seed-generation')?->category?->id,
                $seedTypeCategory?->id,
                $unload->bin_size,
                $unload->receival->paddocks[0] ?? null,
            ]));

            return count($uniqueKey) == 7 ? implode('-', $uniqueKey) : null;
        }
    }

    public static function calculateRemainingReceivals(int $growerId)
    {
        $receivals = Receival::query()
            ->with(['unloads' => fn ($query) => $query->whereNotNull('unique_key')])
            ->where('status', 'completed')
            ->where('grower_id', $growerId)
            ->get();

        static::resetRemainingReceival($growerId);

        foreach ($receivals as $receival) {
            if ($receival->unloads->isEmpty()) {
                continue;
            }

            foreach ($receival->unloads as $unload) {
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

        $uniqueKeys = Unload::query()
            ->whereNotNull('unique_key')
            ->whereRelation('receival', function (Builder $query) use ($growerId) {
                return $query
                    ->where('grower_id', $growerId)
                    ->where('status', 'completed');
            })
            ->pluck('bin_size', 'unique_key')
            ->toArray();

        foreach ($uniqueKeys as $uniqueKey => $binSize) {
            $remainingReceival = RemainingReceival::query()
                ->where('grower_id', $growerId)
                ->where('unique_key', $uniqueKey)
                ->first();

            if (! $remainingReceival) {
                continue;
            }

            $allocations = Allocation::query()
                ->select(['id'])
                ->with(['item'])
                ->where('grower_id', $growerId)
                ->where('unique_key', $uniqueKey)
                ->get();

            foreach ($allocations as $allocation) {
                $remainingReceival->no_of_bins -= $allocation->item->no_of_bins;
                $remainingReceival->weight     -= $allocation->item->weight;
            }

            $remainingReceival->save();
        }

        static::deleteUnsetRemainingReceival($growerId);
    }

    private static function resetRemainingReceival($growerId)
    {
        RemainingReceival::where('grower_id', $growerId)->update([
            'no_of_bins'  => 0,
            'weight'      => 0,
            'receival_id' => [],
            'unload_id'   => [],
        ]);
    }

    private static function deleteUnsetRemainingReceival($growerId)
    {
        RemainingReceival::query()
            ->where('grower_id', $growerId)
            ->whereJsonLength('receival_id', 0)
            ->whereJsonLength('unload_id', 0)
            ->where('weight', 0)
            ->where('no_of_bins', 0)
            ->delete();
    }
}
