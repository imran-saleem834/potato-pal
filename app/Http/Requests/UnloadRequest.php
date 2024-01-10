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
            'no_of_bins' => ['nullable', 'numeric'],
            'weight'     => ['nullable', 'numeric'],
            'channel'    => ['nullable', 'string', Rule::in(['weighbridge', 'BU2', 'BU3'])],
            'system'     => ['nullable', 'numeric', 'max:2'],
            'status'     => ['required', 'string', 'max:20'],
        ];
    }
}
