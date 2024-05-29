<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Reallocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'buyer_id',
        'allocation_buyer_id',
        'comment',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function allocationBuyer()
    {
        return $this->belongsTo(User::class, 'allocation_buyer_id');
    }

    public function item(): MorphOne
    {
        return $this->morphOne(AllocationItem::class, 'allocatable')
            ->where('foreignable_type', Allocation::class);
    }

    public function dispatchItems(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'foreignable')
            ->where('allocatable_type', Dispatch::class)
            ->where('is_returned', false);
    }

    public function returns(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'foreignable')
            ->where('allocatable_type', Dispatch::class)
            ->where('is_returned', true);
    }
}
