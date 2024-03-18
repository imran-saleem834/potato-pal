<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UnloadRequest extends FormRequest
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
            'status'               => ['required', 'string', 'max:20'],
            'fungicide'            => ['nullable', 'array'],
            'unloads'              => ['required', 'array'],
            'unloads.*.seed_type'  => ['required', 'numeric', 'exists:categories,id'],
            'unloads.*.type'       => ['required', 'numeric', Rule::in([1, 2])],
            'unloads.*.bin_size'   => ['required', 'numeric', Rule::in([500, 1000, 2000])],
            'unloads.*.channel'    => ['nullable', 'string', Rule::in(['weighbridge', 'BU2', 'BU3'])],
            'unloads.*.system'     => ['nullable', 'numeric', 'max:2'],
            'unloads.*.no_of_bins' => ['required', 'numeric', 'gt:0'],
            'unloads.*.weight'     => ['required', 'numeric', 'gt:0'],
            'unloads.*.created_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
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
            'unloads.*.seed_type'  => 'seed type',
            'unloads.*.type'       => 'weight type',
            'unloads.*.bin_size'   => 'bin size',
            'unloads.*.channel'    => 'channel',
            'unloads.*.system'     => 'system',
            'unloads.*.no_of_bins' => 'no of bins',
            'unloads.*.weight'     => 'weight',
            'unloads.*.created_at' => 'unload time',
        ];
    }
}
