<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'buyer_id',
        'grower_id',
        'paddock',
        'receival_id',
        'type',
        'comments',
    ];

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where('id', 'LIKE', "%$search%")
            ->orWhere('labelable_type', 'LIKE', "%$search%")
            ->orWhere('labelable_id', 'LIKE', "%$search%")
            ->orWhere('grower_id', 'LIKE', "%$search%")
            ->orWhere('paddock', 'LIKE', "%$search%")
            ->orWhere('receival_id', 'LIKE', "%$search%")
            ->orWhere('type', 'LIKE', "%$search%")
            ->orWhere('comments', 'LIKE', "%$search%")
            ->orWhereRelation('grower', function (Builder $query) use ($search) {
                return $query->where('grower_name', 'LIKE', "%{$search}%");
            });
    }

    public function labelable()
    {
        return $this->morphTo();
    }

    public function grower()
    {
        return $this->belongsTo(User::class, 'grower_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function receival()
    {
        return $this->belongsTo(Receival::class, 'receival_id');
    }
}
