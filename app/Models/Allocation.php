<?php

namespace App\Models;

use App\Models\Scopes\HideMergerScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allocation extends Model
{
    use HasFactory;

    const CATEGORY_INPUTS = [
        'grower_group',
        'seed_type',
        'seed_variety',
        'seed_generation',
        'seed_class',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'buyer_id',
        'grower_id',
        'unique_key',
        'paddock',
        'comment',
        'is_merger',
        'merger_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_merger' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new HideMergerScope);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function grower()
    {
        return $this->belongsTo(User::class, 'grower_id');
    }

    public function item(): MorphOne
    {
        return $this->morphOne(AllocationItem::class, 'allocatable')
            ->whereNull('foreignable_type')
            ->whereNull('returned_id');
    }

    public function cuttingItems(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'foreignable')
            ->where('allocatable_type', Cutting::class)
            ->whereNull('returned_id');
    }

    public function dispatchItems(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'foreignable')
            ->where('allocatable_type', Dispatch::class)
            ->whereNull('returned_id');
    }

    public function returnItems(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'foreignable')
            ->where('allocatable_type', Dispatch::class)
            ->whereNotNull('returned_id');
    }

    public function sizing(): MorphOne
    {
        return $this->morphOne(Sizing::class, 'sizeable');
    }

    public function baggings(): HasMany
    {
        return $this->hasMany(BulkBagging::class);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }
}
