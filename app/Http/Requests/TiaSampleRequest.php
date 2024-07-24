<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TiaSampleRequest extends FormRequest
{
    private array $arrayInputs = [
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
    ];

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
            'inspection_date'     => ['nullable', 'date'],
            'size'                => ['nullable', 'string', 'max:50'],
            'excessive_dirt'      => ['nullable', 'boolean'],
            'skin_russeting'      => ['nullable', 'boolean'],
            'minor_skin_cracking' => ['nullable', 'boolean'],
            'silver_scurf'        => ['nullable', 'boolean'],
            'skinning'            => ['nullable', 'boolean'],
            'black_dot'           => ['nullable', 'boolean'],
            'regrading'           => ['nullable', 'boolean'],
            'comment'             => ['nullable', 'string', 'max:191'],
            'status'              => ['required', 'string', 'max:20'],
        ];

        foreach ($this->arrayInputs as $arrayInput) {
            $rules[$arrayInput]     = ['nullable', 'array'];
            $rules["$arrayInput.*"] = ['nullable', 'numeric'];
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        foreach ($this->arrayInputs as $arrayInput) {
            $values = $this->input($arrayInput);

            foreach ($values ?? [] as $index => $value) {
                $values[$index] = str_replace('%', '', $value);
            }

            $this->merge([$arrayInput => $values]);
        }

        if ($this->input('inspection_date') === 'Invalid date') {
            $this->merge(['inspection_date' => null]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tubers.*'                     => 'tuber',
            'dry_rot.*'                    => 'dry rot',
            'black_scurf.*'                => 'black scurf',
            'powdery_scab.*'               => 'powdery scab',
            'root_knot_nematode.*'         => 'rootknot nematode',
            'soft_rots.*'                  => 'soft rots',
            'pink_rot.*'                   => 'pink rot',
            'sub_total.*'                  => 'sub total',
            'common_scab.*'                => 'common scab',
            'total_disease.*'              => 'total disease',
            'black_scurf_scatter.*'        => 'black scurf scatter',
            'insect_damage.*'              => 'insect damage',
            'malformed_tubers.*'           => 'malformed tubers',
            'mechanical_damage.*'          => 'mechanical damage',
            'stem_end_discolour.*'         => 'stem end discolour',
            'foreign_cultivars.*'          => 'foreign cultivars',
            'misc_frost.*'                 => 'misc frost',
            'total_defects.*'              => 'total defects',
            'minimal_insect_feeding.*'     => 'minimal insect feeding',
            'oversize.*'                   => 'oversize',
            'undersize.*'                  => 'undersize',
            'disease_powdery_scab.*'       => 'powdery scab',
            'disease_root_knot_nematode.*' => 'rootknot nematode',
            'disease_common_scab.*'        => 'common scab',
        ];
    }
}
