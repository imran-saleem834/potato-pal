<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'allocation_buyer_id',
        'reallocation_id',
        'allocation_id',
        'no_of_bins',
        'weight',
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

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function reallocation()
    {
        return $this->belongsTo(Reallocation::class);
    }
}
