<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllocationItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'allocatable_type',
        'allocatable_id',
        'foreignable_type',
        'foreignable_id',
        'returned_id',
        'bin_size',
        'no_of_bins',
        'weight',
        'from_half_tonnes',
        'from_one_tonnes',
        'from_two_tonnes',
        'half_tonnes',
        'one_tonnes',
        'two_tonnes',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'waste',
    ];

    public function getWasteAttribute(): float
    {
        if (in_array($this->getAttribute('allocatable_type'), [Cutting::class, Reallocation::class])) {
            $fromBinsInKg = $this->getAttribute('from_half_tonnes') * 500 +
                            $this->getAttribute('from_one_tonnes') * 1000 +
                            $this->getAttribute('from_two_tonnes') * 2000;

            $toBinsInKg = $this->getAttribute('half_tonnes') * 500 +
                          $this->getAttribute('one_tonnes') * 1000 +
                          $this->getAttribute('two_tonnes') * 2000;

            return $fromBinsInKg - $toBinsInKg;
        }
        return 0;
    }

    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function foreignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function returns(): BelongsTo
    {
        return $this->belongsTo(DispatchReturn::class, 'returned_id');
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }
}
