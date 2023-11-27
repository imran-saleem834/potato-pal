<?php

namespace App\Http\Requests;

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
            'receival_id'          => ['required', 'exists:receivals,id'],
            'total_seed_bins'      => ['nullable', 'numeric'],
            'weight_seed_bins'     => ['nullable', 'string', 'max:50'],
            'total_oversize_bins'  => ['nullable', 'numeric'],
            'weight_oversize_bins' => ['nullable', 'string', 'max:50'],
        ];
    }
}
