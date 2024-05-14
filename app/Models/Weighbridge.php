<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weighbridge extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'unload_id',
        'channel',
        'bin_size',
        'system',
        'no_of_bins',
        'weight',
    ];

    public function unload(): BelongsTo
    {
        return $this->belongsTo(Unload::class);
    }
}
