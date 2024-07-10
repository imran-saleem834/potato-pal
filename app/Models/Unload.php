<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unload extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'receival_id',
        'unique_key',
        'type',
        'bin_size',
        'channel',
        'system',
        'no_of_bins',
        'weight',
    ];

    public function receival()
    {
        return $this->belongsTo(Receival::class);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }

    public function weighbridges(): HasMany
    {
        return $this->hasMany(Weighbridge::class);
    }
}
