<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Laravel\Fortify\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class CuttingRequest extends FormRequest
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
            'buyer_id'                                        => ['required', 'numeric', 'exists:users,id'],
            'cut_date'                                        => ['nullable', 'date'],
            'cut_by'                                          => ['nullable', 'string', 'max:100'],
            'comment'                                         => ['nullable', 'string', 'max:255'],
            'fungicide'                                       => ['nullable', 'array'],
            'fungicide.*'                                     => ['nullable', 'numeric'],
            'selected_allocations'                            => ['required', 'array'],
            'selected_allocations.*.allocation_id'            => ['required', 'numeric', 'exists:allocations,id'],
            'selected_allocations.*.no_of_bins_after_cutting' => ['required', 'numeric'],
            'selected_allocations.*.weight_after_cutting'     => ['required', 'numeric'],
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
            'fungicide.*'                                     => 'fungicide',
            'selected_allocations'                            => 'allocation',
            'selected_allocations.*.allocation_id'            => 'allocation',
            'selected_allocations.*.no_of_bins_after_cutting' => 'no of bins',
            'selected_allocations.*.weight_after_cutting'     => 'weight',
        ];
    }
}
