<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'labelable_id',
        'labelable_type',
        'receival_id',
        'type',
        'comments',
    ];

    public function labelable()
    {
        return $this->morphTo();
    }
}
