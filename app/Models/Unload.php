<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'total_seed_bins',
        'weight_seed_bins',
        'total_oversize_bins',
        'weight_oversize_bins',
        'status',
    ];

    public function receival()
    {
        return $this->belongsTo(Receival::class);
    }
}
