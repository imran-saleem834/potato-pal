<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'no_of_bins',
        'weight',
        'bin_size',
        'paddock',
        'comment',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function grower()
    {
        return $this->belongsTo(User::class, 'grower_id');
    }

    public function cuttings()
    {
        return $this->hasMany(CuttingAllocation::class);
    }

    public function reallocations()
    {
        return $this->hasMany(Reallocation::class);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }
}
