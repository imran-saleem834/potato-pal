<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use App\Models\Reallocation;
use Illuminate\Validation\Rule;
use App\Models\CuttingAllocation;
use Illuminate\Foundation\Http\FormRequest;

class LabelRequest extends FormRequest
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
            'labelable_type' => [
                'required',
                'string',
                Rule::in([Allocation::class, Reallocation::class, CuttingAllocation::class]),
            ],
            'labelable_id'   => ['required', 'numeric'],
            'buyer_id'       => ['required', 'numeric', 'exists:users,id'],
            'grower_id'      => ['required', 'numeric', 'exists:users,id'],
            'paddock'        => ['required', 'string', 'max:50'],
            'receival_id'    => ['nullable', 'numeric', 'exists:receivals,id'],
            'type'           => ['required', 'string', Rule::in(['rec-1', 'rec-3', 'rec-id', 'cut-seed'])],
            'comments'       => ['nullable', 'string', 'max:191'],
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
            'labelable_type' => 'Label Type',
            'labelable_id'   => 'Label Record',
            'buyer_id'       => 'Issue to',
        ];
    }
}
