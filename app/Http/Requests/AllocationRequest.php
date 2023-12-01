<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocationRequest extends FormRequest
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
            'buyer_id'                   => ['required', 'numeric', 'exists:users,id'],
            'grower_id'                  => ['nullable', 'numeric', 'exists:users,id'],
            'allocated_bins'             => ['nullable', 'numeric'],
            'allocated_tonnes'           => ['nullable', 'numeric'],
            'tonnes_available_receivals' => ['nullable', 'numeric'],
            'bins_before_cutting'        => ['nullable', 'numeric'],
            'tonnes_before_cutting'      => ['nullable', 'numeric'],
            'cutting_date'               => ['nullable', 'date'],
            'bins_after_cutting'         => ['nullable', 'numeric'],
            'tonnes_after_cutting'       => ['nullable', 'numeric'],
            'reallocated_buyer_id'       => ['nullable', 'numeric', 'exists:users,id'],
            'tonnes_reallocated'         => ['nullable', 'numeric'],
            'bins_reallocated'           => ['nullable', 'numeric'],
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
            'buyer_id'             => 'buyer',
            'grower_id'            => 'grower',
            'reallocated_buyer_id' => 'reallocated buyer',
        ];
    }
}
