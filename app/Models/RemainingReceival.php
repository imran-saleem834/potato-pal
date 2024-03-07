<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RemainingReceival extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'grower_id',
        'unique_key',
        'receival_id',
        'unload_id',
        'no_of_bins',
        'weight',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'receival_id' => 'array',
        'unload_id'   => 'array',
    ];
}
