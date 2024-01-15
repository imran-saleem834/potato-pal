<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Receival extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'grower_id',
        'paddocks',
        'grower_docket_no',
        'chc_receival_docket_no',
        'driver_name',
        'comments',
        'status',
        'images',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'paddocks' => 'array',
        'images'   => 'array',
    ];

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where('id', 'LIKE', "%$search%")
            ->orWhere('paddocks', 'LIKE', "%$search%")
            ->orWhere('grower_docket_no', 'LIKE', "%$search%")
            ->orWhere('chc_receival_docket_no', 'LIKE', "%$search%")
            ->orWhere('driver_name', 'LIKE', "%$search%")
            ->orWhere('comments', 'LIKE', "%$search%")
            ->orWhere('status', 'LIKE', "%$search%");
    }

    public function grower()
    {
        return $this->belongsTo(User::class, 'grower_id');
    }

    public function tiaSample()
    {
        return $this->hasOne(TiaSample::class);
    }

    public function unloads(): HasMany
    {
        return $this->hasMany(Unload::class);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }
}
