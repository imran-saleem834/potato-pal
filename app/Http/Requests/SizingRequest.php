<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SizingRequest extends FormRequest
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
            'items'                  => ['required', 'array'],
            'items.*.seed_type'      => ['required', 'numeric', 'exists:categories,id'],
            'items.*.fungicide'      => ['nullable', 'numeric', 'exists:categories,id'],
            'items.*.half_tonnes'    => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'items.*.one_tonnes'     => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'items.*.two_tonnes'     => ['nullable', 'numeric', 'gte:0', 'max:999999'],
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
            'items.*.seed_type'      => 'seed type',
            'items.*.fungicide'      => 'fungicide type',
            'items.*.half_tonnes'    => 'half tonnes',
            'items.*.one_tonnes'     => 'one tonnes',
            'items.*.two_tonnes'     => 'two_tonnes',
        ];
    }
}
