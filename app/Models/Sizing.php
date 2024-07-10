<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'bins_tipped',
        'weight',
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
        'bins_tipped' => 'array',
    ];

    protected function start(): Attribute
    {
        return Attribute::make(fn ($value) => substr($value, 0, 5));
    }

    protected function end(): Attribute
    {
        return Attribute::make(fn ($value) => substr($value, 0, 5));
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }

    public function sizeable()
    {
        return $this->morphTo();
    }
}
