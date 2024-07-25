<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sizing extends Model
{
    use HasFactory;

    const CATEGORY_INPUTS = ['seed_type', 'fungicide'];

    const CATEGORY_TYPES = ['seed-type', 'fungicide'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'sizeable_id',
        'sizeable_type',
        'start',
        'end',
        'no_of_crew',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sizeable()
    {
        return $this->morphTo();
    }

    public function items(): MorphMany
    {
        return $this->morphMany(AllocationItem::class, 'allocatable')
            ->whereNull('returned_id');
    }
}
