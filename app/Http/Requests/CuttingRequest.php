<?php

namespace App\Http\Requests;

use App\Models\Cutting;
use Illuminate\Validation\Rule;
use App\Helpers\AllocationHelper;
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
        $rules = [
            'buyer_id'                          => ['required', 'numeric', 'exists:users,id'],
            'half_tonnes'                       => ['nullable', 'numeric'],
            'one_tonnes'                        => ['nullable', 'numeric'],
            'two_tonnes'                        => ['nullable', 'numeric'],
            'cut_date'                          => ['nullable', 'date'],
            'cool_store'                        => ['nullable', 'array'],
            'cool_store.*'                      => ['nullable', 'numeric'],
            'comment'                           => ['nullable', 'string', 'max:255'],
            'fungicide'                         => ['nullable', 'array'],
            'fungicide.*'                       => ['nullable', 'numeric'],
            'selected_allocation'               => ['required', 'array'],
            'selected_allocation.id'            => ['nullable', 'numeric', 'exists:allocations,id'],
            'selected_allocation.item'          => ['required', 'array'],
            'selected_allocation.item.bin_size' => ['required', 'numeric', Rule::in([500, 1000, 2000])],
        ];

        $inputs     = $this->input('selected_allocation', []);
        $allocation = AllocationHelper::getAvailableAllocationForCutting(['id' => $inputs['id']])->first();

        $binsInKg = $allocation->available_no_of_bins * $allocation->item->bin_size;

        if ($this->isMethod('PATCH')) {
            $cutting = Cutting::query()
                ->with(['item' => fn ($query) => $query->where('foreignable_id', $allocation->id)])
                ->find($this->route('cutting'));

            if (! empty($cutting->item)) {
                $binsInKg += $cutting->item->no_of_bins * $cutting->item->bin_size;
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
            'fungicide.*'                    => 'fungicide',
            'selected_allocation'            => 'allocation',
            'selected_allocation.id'         => 'allocation',
            'selected_allocation.no_of_bins' => 'no of bins to cut',
        ];
    }
}
