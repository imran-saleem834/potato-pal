<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TiaSample extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'receival_id',
        'status',
        'inspection_date',
        'size',
        'tubers',
        'dry_rot',
        'black_scurf',
        'powdery_scab',
        'root_knot_nematode',
        'soft_rots',
        'pink_rot',
        'sub_total',
        'common_scab',
        'total_disease',
        'black_scurf_scatter',
        'insect_damage',
        'malformed_tubers',
        'mechanical_damage',
        'stem_end_discolour',
        'foreign_cultivars',
        'misc_frost',
        'total_defects',
        'minimal_insect_feeding',
        'oversize',
        'undersize',
        'disease_powdery_scab',
        'disease_root_knot_nematode',
        'disease_common_scab',
        'excessive_dirt',
        'skin_russeting',
        'minor_skin_cracking',
        'silver_scurf',
        'skinning',
        'black_dot',
        'regrading',
        'comment',
        'images',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inspection_date'            => 'date',
        'tubers'                     => 'array',
        'dry_rot'                    => 'array',
        'black_scurf'                => 'array',
        'powdery_scab'               => 'array',
        'root_knot_nematode'         => 'array',
        'soft_rots'                  => 'array',
        'pink_rot'                   => 'array',
        'sub_total'                  => 'array',
        'common_scab'                => 'array',
        'total_disease'              => 'array',
        'black_scurf_scatter'        => 'array',
        'insect_damage'              => 'array',
        'malformed_tubers'           => 'array',
        'mechanical_damage'          => 'array',
        'stem_end_discolour'         => 'array',
        'foreign_cultivars'          => 'array',
        'misc_frost'                 => 'array',
        'total_defects'              => 'array',
        'minimal_insect_feeding'     => 'array',
        'oversize'                   => 'array',
        'undersize'                  => 'array',
        'disease_powdery_scab'       => 'array',
        'disease_root_knot_nematode' => 'array',
        'disease_common_scab'        => 'array',
        'images'                     => 'array',
        'excessive_dirt'             => 'boolean',
        'minor_skin_cracking'        => 'boolean',
        'skinning'                   => 'boolean',
        'regarding'                  => 'boolean',
    ];

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function (Builder $query) use ($search) {
            if (str_contains($search, ':')) {
                [$column, $search] = explode(':', $search);
                if (Schema::hasColumn('tia_samples', $column)) {
                    return $query->where($column, 'LIKE', "%$search%");
                }
            }
            return $query
                ->where('id', 'LIKE', "%$search%")
                ->orWhere('status', 'LIKE', "%$search%")
                ->orWhere('inspection_date', 'LIKE', "%$search%")
                ->orWhere('size', 'LIKE', "%$search%")
                ->orWhere('comment', 'LIKE', "%$search%")
                ->orWhereRelation('receival', function (Builder $query) use ($search) {
                    return $query
                        ->where('id', 'LIKE', "%$search%")
                        ->where('paddocks', 'LIKE', "%$search%")
                        ->orWhere('grower_docket_no', 'LIKE', "%$search%")
                        ->orWhere('chc_receival_docket_no', 'LIKE', "%$search%")
                        ->orWhereRelation('categories.category', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('grower', function (Builder $query) use ($search) {
                            return $query
                                ->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('grower_name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('grower.categories.category', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('unloads', function (Builder $catQuery) use ($search) {
                            return $catQuery
                                ->where('channel', 'LIKE', "%{$search}%")
                                ->orWhere('no_of_bins', 'LIKE', "%{$search}%")
                                ->orWhere('system', 'LIKE', "%{$search}%")
                                ->orWhere('weight', 'LIKE', "%{$search}%")
                                ->orWhere('bin_size', 'LIKE', "%{$search}%");
                        })
                        ->orWhereRelation('unloads.categories.category', 'name', 'LIKE', "%{$search}%");
                });
        });
    }

    public function receival()
    {
        return $this->belongsTo(Receival::class);
    }
}
