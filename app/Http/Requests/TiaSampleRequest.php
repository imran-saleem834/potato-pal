<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TiaSampleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'receival_id'              => ['required', 'exists:receivals,id', 'unique:tia_samples,receival_id'],
            'processor'                => ['nullable', 'string', 'max:20'],
            'inspection_no'            => ['nullable', 'string', 'max:20'],
            'inspection_date'          => ['nullable', 'date'],
            'cool_store'               => ['nullable', 'string', 'max:100'],
            'size'                     => ['nullable', 'string', 'max:50'],
            'tubers'                   => ['nullable', 'array'],
            'tubers.*'                 => ['nullable', 'numeric'],
            'dry_rot'                  => ['nullable', 'array'],
            'dry_rot.*'                => ['nullable', 'numeric'],
            'black_scurf'              => ['nullable', 'array'],
            'black_scurf.*'            => ['nullable', 'numeric'],
            'powdery_scab'             => ['nullable', 'array'],
            'powdery_scab.*'           => ['nullable', 'numeric'],
            'root_knot_nematode'       => ['nullable', 'array'],
            'root_knot_nematode.*'     => ['nullable', 'numeric'],
            'soft_rots'                => ['nullable', 'array'],
            'soft_rots.*'              => ['nullable', 'numeric'],
            'pink_rot'                 => ['nullable', 'array'],
            'pink_rot.*'               => ['nullable', 'numeric'],
            'black_scurf_scatter'      => ['nullable', 'array'],
            'black_scurf_scatter.*'    => ['nullable', 'numeric'],
            'insect_damage'            => ['nullable', 'array'],
            'insect_damage.*'          => ['nullable', 'numeric'],
            'malformed_tubers'         => ['nullable', 'array'],
            'malformed_tubers.*'       => ['nullable', 'numeric'],
            'mechanical_damage'        => ['nullable', 'array'],
            'mechanical_damage.*'      => ['nullable', 'numeric'],
            'stem_end_discolour'       => ['nullable', 'array'],
            'stem_end_discolour.*'     => ['nullable', 'numeric'],
            'foreign_cultivars'        => ['nullable', 'array'],
            'foreign_cultivars.*'      => ['nullable', 'numeric'],
            'misc_frost'               => ['nullable', 'array'],
            'misc_frost.*'             => ['nullable', 'numeric'],
            'minimal_insect_feeding'   => ['nullable', 'array'],
            'minimal_insect_feeding.*' => ['nullable', 'numeric'],
            'oversize'                 => ['nullable', 'array'],
            'oversize.*'               => ['nullable', 'numeric'],
            'undersize'                => ['nullable', 'array'],
            'undersize.*'              => ['nullable', 'numeric'],
            'status'                   => ['required', 'string', 'max:20'],
        ];

        if ($this->isMethod('PATCH')) {
            $rules['receival_id'] = [
                'required',
                'exists:receivals,id',
                Rule::unique('tia_samples')->ignore($this->route('tia_sample'))
            ];
        }

        return $rules;
    }
}
