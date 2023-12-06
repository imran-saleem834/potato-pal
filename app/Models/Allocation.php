<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'buyer_id',
        'grower_id',
        'unique_key',
        'allocated_type',
        'allocated_bins',
        'allocated_tonnes',
        'bins_before_cutting',
        'tonnes_before_cutting',
        'cutting_date',
        'bins_after_cutting',
        'tonnes_after_cutting',
        'reallocated_buyer_id',
        'tonnes_reallocated',
        'bins_reallocated',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function grower()
    {
        return $this->belongsTo(User::class, 'grower_id');
    }

    public function reallocatedBuyer()
    {
        return $this->belongsTo(User::class, 'reallocated_buyer_id');
    }

    public function setCuttingDateAttribute($date)
    {
        $this->attributes['cutting_date'] = str_replace('T', ' ', $date);
    }
}
