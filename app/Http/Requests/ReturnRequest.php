<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'dispatch'   => ['required', 'array'],
            'no_of_bins' => ['required', 'numeric', 'gt:0'],
            'bin_size'   => ['required', 'numeric', Rule::in([500, 1000, 2000])],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $weight = max($this->input('weight', 0), 0) * 1000;
        $this->merge(['weight' => $weight]);
    }
}
