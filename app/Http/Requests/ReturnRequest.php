<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRequest extends FormRequest
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
            'dispatch'    => ['required', 'array'],
            'half_tonnes' => ['nullable', 'numeric'],
            'one_tonnes'  => ['nullable', 'numeric'],
            'two_tonnes'  => ['nullable', 'numeric'],
        ];
    }
}
