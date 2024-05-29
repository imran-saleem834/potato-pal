<?php

namespace App\Http\Requests;

use App\Models\Dispatch;
use Illuminate\Validation\Rule;
use App\Helpers\AllocationHelper;
use Illuminate\Foundation\Http\FormRequest;

class DispatchRequest extends FormRequest
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
            'type'                              => ['required', 'string', Rule::in(['allocation', 'reallocation'])],
            'allocation_buyer_id'               => ['required', 'numeric', 'exists:users,id'],
            'selected_allocation.id'            => ['nullable', 'numeric'],
            'selected_allocation.item'          => ['required', 'array'],
            'selected_allocation.item.bin_size' => ['required', 'numeric', Rule::in([500, 1000, 2000])],
            'comment'                           => ['nullable', 'string', 'max:255'],
        ];

        $inputs = $this->input('selected_allocation', []);

        $allocation = AllocationHelper::getAvailableAllocationForDispatch([$inputs['type'].'_id' => $inputs['id']])
            ->where('type', $inputs['type'])
            ->first();

        $binsInKg = $allocation->available_no_of_bins * $allocation->item->bin_size;
        if ($this->isMethod('PATCH')) {
            $dispatch = Dispatch::query()
                ->with(['item' => fn ($query) => $query->where('foreignable_id', $allocation->id)])
                ->find($this->route('dispatch'));

            if (! empty($dispatch->item)) {
                $binsInKg += $dispatch->item->no_of_bins * $dispatch->item->bin_size;
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
            'buyer_id'                          => 'dispatch buyer',
            'allocation_buyer_id'               => 'allocation buyer',
            'selected_allocation'               => 'allocation',
            'selected_allocation.no_of_bins'    => 'no of bins',
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
