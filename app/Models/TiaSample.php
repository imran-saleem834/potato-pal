<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'processor',
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
        'disease_scoring',
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

    public function receival()
    {
        return $this->belongsTo(Receival::class);
    }
}
