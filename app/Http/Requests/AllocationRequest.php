<?php

namespace App\Http\Requests;

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
        $rules = [
            'grower_id'       => ['required', 'numeric', 'exists:users,id'],
            'unique_key'      => ['required', 'string'],
            'no_of_bins'      => ['required', 'numeric'],
            'weight'          => ['required', 'numeric'],
            'bin_size'        => ['required', 'numeric', Rule::in([0.5, 1, 2])],
            'paddock'         => ['required', 'string'],
            'grower'          => ['required', 'array', 'max:1'],
            'seed_variety'    => ['required', 'array', 'max:1'],
            'seed_generation' => ['required', 'array', 'max:1'],
            'seed_class'      => ['required', 'array', 'max:1'],
            'seed_type'       => ['required', 'array', 'max:1'],
        ];

        if ($this->isMethod('POST')) {
            $rules['buyer_id'] = ['required', 'numeric', 'exists:users,id'];
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
            'buyer_id' => 'buyer',
            'unique_key' => 'receival',
            //            'grower_id'            => 'grower',
            //            'reallocated_buyer_id' => 'reallocated buyer',
        ];
    }
}
