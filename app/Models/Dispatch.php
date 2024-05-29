<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'buyer_id',
        'type',
        'allocation_buyer_id',
        'comment',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function allocationBuyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocation_buyer_id');
    }

    public function item(): MorphOne
    {
        return $this->morphOne(AllocationItem::class, 'allocatable')
            ->where('is_returned', false);
    }

    public function returns(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'allocatable')
            ->where('is_returned', true);
    }
}
