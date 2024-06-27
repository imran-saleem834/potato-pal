<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'half_tonnes',
        'one_tonnes',
        'two_tonnes',
    ];

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
}
