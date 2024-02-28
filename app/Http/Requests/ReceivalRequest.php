<?php

namespace App\Http\Requests;

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
            'grower_group'           => ['nullable', 'array', 'max:1'],
            'paddocks'               => ['nullable', 'array', 'max:1'],
            'seed_variety'           => ['nullable', 'array', 'max:1'],
            'seed_generation'        => ['nullable', 'array', 'max:1'],
            'seed_class'             => ['nullable', 'array', 'max:1'],
            'transport'              => ['nullable', 'array', 'max:1'],
            'delivery_type'          => ['nullable', 'array'],
            'grower_docket_no'       => ['nullable', 'string', 'max:50'],
            'chc_receival_docket_no' => ['nullable', 'string', 'max:50'],
            'driver_name'            => ['nullable', 'string', 'max:80'],
            'comments'               => ['nullable', 'string', 'max:191'],
            'created_at'             => ['nullable', 'date_format:Y-m-d\TH:i'],
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
            'grower_id'  => 'grower',
            'created_at' => 'receival time',
        ];
    }
}
