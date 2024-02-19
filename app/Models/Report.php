<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    const TYPES = [
        'receival'     => 'Receival',
        'unload'       => 'Unload',
        'tia-sample'   => 'Tia Sample',
        'allocation'   => 'Allocation',
        'reallocation' => 'Reallocation',
        'label'        => 'Label',
        'dispatch'     => 'Dispatch',
        'cutting'      => 'Cutting',
        'weighbridge'  => 'Weighbridge',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'type',
        'filters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'filter',
    ];

    public function getFilterAttribute(): string
    {
        $filter = [];
        if (!empty($this->filters['grower_ids']) || !empty($this->filters['grower_groups']) || !empty($this->filters['paddocks'])) {
            $filter[] = 'Grower';
        }
        if (!empty($this->filters['start']) || !empty($this->filters['end'])) {
            $filter[] = 'Range';
        }
        if (!empty($this->filters['seed_varieties']) || !empty($this->filters['seed_generations']) || !empty($this->filters['seed_classes'])) {
            $filter[] = 'Seed';
        }
        if (!empty($this->filters['fungicides']) || !empty($this->filters['transports'])) {
            $filter[] = 'Unload';
        }
        if (empty($filter)) {
            $filter[] = 'No Filter';
        }

        return implode(', ', $filter);
    }
}
