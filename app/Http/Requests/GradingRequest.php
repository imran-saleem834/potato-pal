<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'type'                   => ['required', 'string', Rule::in(['allocation', 'unload'])],
            'selected_allocation'    => ['required_if:type,allocation', 'array'],
            'selected_allocation.id' => ['required_if:type,allocation', 'numeric', 'exists:allocations,id'],
            'selected_unload'        => ['required_if:type,unload', 'array'],
            'selected_unload.id'     => ['required_if:type,unload', 'numeric', 'exists:unloads,id'],
            'user_id'                => ['required', 'numeric', 'exists:users,id'],
            'bins_tipped'            => ['nullable', 'array'],
            'bins_tipped.*'          => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'bins_graded'            => ['nullable', 'array'],
            'bins_graded.*'          => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'weight'                 => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'waste'                  => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'start'                  => ['nullable', 'date_format:Y-m-d H:i:s'],
            'end'                    => ['nullable', 'date_format:Y-m-d H:i:s'],
            'no_of_crew'             => ['nullable', 'numeric', 'gte:0', 'max:999999'],
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
            'selected_allocation'    => 'allocation',
            'selected_allocation.id' => 'allocation',
            'selected_unload'        => 'unload',
            'selected_unload.id'     => 'unload',
        ];
    }
}
