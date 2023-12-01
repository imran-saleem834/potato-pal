<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'user_id',
        'paddocks',
        'oversize_bin_size',
        'seed_bin_size',
        'grower_docket_no',
        'chc_receival_docket_no',
        'driver_name',
        'comments',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unload()
    {
        return $this->hasOne(Unload::class);
    }

    public function tiaSample()
    {
        return $this->hasOne(TiaSample::class);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }
}
