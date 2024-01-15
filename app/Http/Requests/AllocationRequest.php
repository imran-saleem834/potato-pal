<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AllocationRequest extends FormRequest
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
        $receival = $this->get('select_receival');
        $noOfBins = $receival['no_of_bins'] ?? 0;
        $weight   = $receival['weight'] ?? 0;

        $rules = [
            'buyer_id'        => ['required', 'numeric', 'exists:users,id'],
            'grower_id'       => ['required', 'numeric', 'exists:users,id'],
            'unique_key'      => ['required', 'string'],
            'no_of_bins'      => ['required', 'numeric', 'gt:0', "max:$noOfBins"],
            'weight'          => ['required', 'numeric', 'gt:0', "max:$weight"],
            'bin_size'        => ['required', 'numeric', Rule::in([500, 1000, 2000])],
            'paddock'         => ['required', 'string'],
            'comment'         => ['nullable', 'string', 'max:255'],
            'grower_group'    => ['required', 'array', 'max:1'],
            'seed_variety'    => ['required', 'array', 'max:1'],
            'seed_generation' => ['required', 'array', 'max:1'],
            'seed_class'      => ['required', 'array', 'max:1'],
            'seed_type'       => ['required', 'array', 'max:1'],
        ];

        if ($this->isMethod('PATCH')) {
            $allocation = Allocation::find($this->route('allocation'));
            if ($receival['unique_key'] === $allocation->unique_key) {
                $noOfBins = $allocation->no_of_bins + $noOfBins;
                $weight   = $allocation->weight + $weight;
            }

            $rules['no_of_bins'] = ['required', 'numeric', 'gt:0', "max:$noOfBins"];
            $rules['weight']     = ['required', 'numeric', 'gt:0', "max:$weight"];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'buyer_id'   => 'buyer',
            'grower_id'  => 'grower',
            'unique_key' => 'receival',
        ];
    }
}
