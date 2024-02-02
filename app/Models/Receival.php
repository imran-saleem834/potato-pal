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

    const CATEGORY_TYPES = [
        'grower-group',
        'seed-variety',
        'seed-generation',
        'seed-class',
        'delivery-type',
        'transport'
    ];
    CONST CATEGORY_INPUTS = ['buyer_group', 'grower_group', 'cool_store'];

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
            ->orWhere('status', 'LIKE', "%$search%")
            ->orWhereRelation('categories.category', function (Builder $catQuery) use ($search) {
                return $catQuery->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereRelation('grower', function (Builder $query) use ($search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('grower_name', 'LIKE', "%{$search}%");
            })
            ->orWhereRelation('grower.categories.category', function (Builder $catQuery) use ($search) {
                return $catQuery->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereRelation('unloads', function (Builder $catQuery) use ($search) {
                return $catQuery->where('channel', 'LIKE', "%{$search}%")
                    ->orWhere('no_of_bins', 'LIKE', "%{$search}%")
                    ->orWhere('system', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(`weight`, ' kg') LIKE '%{$search}%'")
                    ->orWhereRaw("CONCAT(`bin_size`, ' kg') LIKE '%{$search}%'");
            })
            ->orWhereRelation('unloads.categories.category', function (Builder $catQuery) use ($search) {
                return $catQuery->where('name', 'LIKE', "%{$search}%");
            });
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
