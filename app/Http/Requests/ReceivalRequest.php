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
            'user_id'                => ['required', 'exists:users,id'],
            'grower'                 => ['nullable', 'array'],
            'paddocks'               => ['nullable', 'array'],
            'seed_variety'           => ['nullable', 'array'],
            'seed_generation'        => ['nullable', 'array'],
            'seed_class'             => ['nullable', 'array'],
            'seed_type'              => ['nullable', 'array'],
            'oversize_bin_size'      => ['nullable', 'string', 'max:20'],
            'seed_bin_size'          => ['nullable', 'string', 'max:20'],
            'transport'              => ['nullable', 'array'],
            'delivery_type'          => ['nullable', 'array'],
            'grower_docket_no'       => ['nullable', 'string', 'max:50'],
            'chc_receival_docket_no' => ['nullable', 'string', 'max:50'],
            'fungicide'              => ['nullable', 'array'],
            'driver_name'            => ['nullable', 'string', 'max:80'],
            'comments'               => ['nullable', 'string', 'max:255'],
        ];
    }
}
