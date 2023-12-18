<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ReceivalRequest extends FormRequest
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
        return [
            'grower_id'              => ['required', 'exists:users,id'],
            'grower'                 => ['nullable', 'array', 'max:1'],
            'paddocks'               => ['nullable', 'array', 'max:1'],
            'seed_variety'           => ['nullable', 'array', 'max:1'],
            'seed_generation'        => ['nullable', 'array', 'max:1'],
            'seed_class'             => ['nullable', 'array', 'max:1'],
            'seed_type'              => ['nullable', 'array', 'max:1'],
            'bin_size'               => ['nullable', 'numeric', Rule::in([0.5, 1, 2]),],
            'transport'              => ['nullable', 'array', 'max:1'],
            'delivery_type'          => ['nullable', 'array'],
            'grower_docket_no'       => ['nullable', 'string', 'max:50'],
            'chc_receival_docket_no' => ['nullable', 'string', 'max:50'],
            'fungicide'              => ['nullable', 'array'],
            'driver_name'            => ['nullable', 'string', 'max:80'],
            'comments'               => ['nullable', 'string', 'max:191'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'grower_id' => 'grower',
            'grower'    => 'grower group',
        ];
    }
}
