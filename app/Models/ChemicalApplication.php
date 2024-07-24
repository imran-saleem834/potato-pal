<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChemicalApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'buyer_id',
        'allocation_id',
        'bins_tipped',
        'bins_out',
        'fungicide',
        'fungicide_used',
        'start',
        'end',
        'no_of_crew',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fungicide'   => 'boolean',
        'bins_tipped' => 'array',
        'bins_out'    => 'array',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }
}
