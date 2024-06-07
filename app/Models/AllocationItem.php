<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
        'is_returned',
        'bin_size',
        'no_of_bins',
        'weight',
        'half_tonnes',
        'one_tonnes',
        'two_tonnes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_returned' => 'boolean',
    ];

    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function foreignable(): MorphTo
    {
        return $this->morphTo();
    }
}
