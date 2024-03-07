<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cutting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'buyer_id',
        'cut_date',
        'cut_by',
        'comment',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function cuttingAllocations(): HasMany
    {
        return $this->hasMany(CuttingAllocation::class);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }
}
