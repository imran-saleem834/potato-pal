<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    const CATEGORIES = [
        ['value' => 'grading', 'label' => 'Grading'],
        ['value' => 'sizing', 'label' => 'Sizing'],
        ['value' => 'chemical-applicant', 'label' => 'Chemical Applicant'],
        ['value' => 'bulk-bagging', 'label' => 'Bulk Bagging'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'unload_id',
        'category',
        'bins_tipped',
        'whole_seed',
        'oversize',
        'round',
        'cut_sets',
        'waste',
        'no_of_bulk_bags_out',
        'net_weight_bags_out',
        'fungicide',
        'fungicide_used',
        'start',
        'end',
        'no_of_crew',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fungicide'   => 'boolean',
        'bins_tipped' => 'array',
        'whole_seed'  => 'array',
        'oversize'    => 'array',
        'round'       => 'array',
        'cut_sets'    => 'array',
    ];

    protected function start(): Attribute
    {
        return Attribute::make(fn ($value) => substr($value,0,5));
    }

    protected function end(): Attribute
    {
        return Attribute::make(fn ($value) => substr($value,0,5));
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where('id', 'LIKE', "%$search%")
            ->orWhere('category', 'LIKE', "%$search%")
            ->orWhere('no_of_crew', 'LIKE', "%$search%")
            ->orWhere('comments', 'LIKE', "%$search%")
            ->orWhereRelation('unload.receival.grower', function (Builder $query) use ($search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('grower_name', 'LIKE', "%{$search}%");
            });
    }

    public function unload()
    {
        return $this->belongsTo(Unload::class, 'unload_id');
    }
}
