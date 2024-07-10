<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradingRequest extends FormRequest
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
            'selected_allocation'          => ['required', 'array'],
            'selected_allocation.id'       => ['nullable', 'numeric', 'exists:allocations,id'],
            'selected_allocation.buyer_id' => ['nullable', 'numeric', 'exists:users,id'],
            'bins_tipped'                  => ['nullable', 'array'],
            'bins_tipped.*'                => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'bins_graded'                  => ['nullable', 'array'],
            'bins_graded.*'                => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'weight'                       => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'waste'                        => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'start'                        => ['nullable', 'date_format:H:i'],
            'end'                          => ['nullable', 'date_format:H:i'],
            'no_of_crew'                   => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'comments'                     => ['nullable', 'string', 'max:191'],
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
            'selected_allocation'          => 'allocation',
            'selected_allocation.id'       => 'allocation',
            'selected_allocation.buyer_id' => 'allocation',
        ];
    }
}
