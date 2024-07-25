<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SizingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'allocation_items';

    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cuttingItems(): MorphMany
    {
        return $this->morphMany(SizingItem::class, 'foreignable')
            ->where('allocatable_type', Cutting::class)
            ->whereNull('returned_id');
    }

    public function dispatchItems(): MorphMany
    {
        return $this->morphMany(SizingItem::class, 'foreignable')
            ->where('allocatable_type', Dispatch::class)
            ->whereNull('returned_id');
    }

    public function returnItems(): MorphMany
    {
        return $this->morphMany(SizingItem::class, 'foreignable')
            ->where('allocatable_type', Dispatch::class)
            ->whereNotNull('returned_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(CategoriesRelation::class, 'categorizable_id')
            ->where('categorizable_type', AllocationItem::class);
    }
}
