<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'tia_sample_id',
        'unloading_status',
        'unloading_id',
        'oversize_bin_size',
        'seed_bin_size',
        'transport',
        'grower_docket_no',
        'chc_receival_docket_no',
        'driver_name',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
