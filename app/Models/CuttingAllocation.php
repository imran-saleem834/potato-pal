<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuttingAllocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cutting_id',
        'allocation_id',
        'no_of_bins',
    ];

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function cutting()
    {
        return $this->belongsTo(Cutting::class);
    }
}
