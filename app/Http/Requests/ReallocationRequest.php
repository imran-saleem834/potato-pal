<?php

namespace App\Http\Requests;

use App\Models\Reallocation;
use Illuminate\Validation\Rule;
use App\Helpers\AllocationHelper;
use Illuminate\Foundation\Http\FormRequest;

class ReallocationRequest extends FormRequest
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
        $rules = [
            'buyer_id'                          => ['required', 'numeric', 'exists:users,id'],
            'allocation_buyer_id'               => ['required', 'numeric', 'exists:users,id'],
            'comment'                           => ['nullable', 'string', 'max:255'],
            'selected_allocation'               => ['required', 'array'],
            'selected_allocation.id'            => [
                'nullable',
                'numeric',
                'exists:allocations,id',
            ],
            'selected_allocation.item'          => ['required', 'array'],
            'selected_allocation.item.bin_size' => ['required', 'numeric', Rule::in([500, 1000, 2000])],
        ];

        $inputs = $this->input('selected_allocation', []);

        $allocation = AllocationHelper::getAvailableAllocationForReallocation(['id' => $inputs['id']])->first();

        $binsInKg = $allocation->available_no_of_bins * $allocation->item->bin_size;
        if ($this->isMethod('PATCH')) {
            $reallocation = Reallocation::query()
                ->with(['item' => fn ($query) => $query->where('foreignable_id', $allocation->id)])
                ->find($this->route('reallocation'));

            if (! empty($reallocation->item)) {
                $binsInKg += $reallocation->item->no_of_bins * $reallocation->item->bin_size;
            }
        }

        $allocation->available_no_of_bins = $binsInKg / $allocation->item->bin_size;

        $rules['selected_allocation.no_of_bins'] = [
            'required',
            'numeric',
            'gt:0',
            "max:{$allocation->available_no_of_bins}",
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'buyer_id'                          => 'reallocate to buyer',
            'allocation_buyer_id'               => 'reallocate from buyer',
            'selected_allocation'               => 'allocation',
            'selected_allocation.allocation_id' => 'allocation',
            'selected_allocation.no_of_bins'    => 'no of bins',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'weight.gte' => 'The :attribute field must be greater than or equal to :value kg.',
            'weight.max' => 'The :attribute field must not be greater than :max kg.',
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
