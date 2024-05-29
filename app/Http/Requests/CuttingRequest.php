<?php

namespace App\Http\Requests;

use App\Models\Cutting;
use Illuminate\Support\Arr;
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
            'buyer_id'                             => ['required', 'numeric', 'exists:users,id'],
            'half_tonnes'                          => ['nullable', 'numeric'],
            'one_tonnes'                           => ['nullable', 'numeric'],
            'two_tonnes'                           => ['nullable', 'numeric'],
            'cut_date'                             => ['nullable', 'date'],
            'cool_store'                           => ['nullable', 'array'],
            'cool_store.*'                         => ['nullable', 'numeric'],
            'comment'                              => ['nullable', 'string', 'max:255'],
            'fungicide'                            => ['nullable', 'array'],
            'fungicide.*'                          => ['nullable', 'numeric'],
            'selected_allocations'                 => ['required', 'array'],
            'selected_allocations.*.id'            => [
                'nullable',
                'numeric',
                'exists:allocations,id',
            ],
            'selected_allocations.*.no_of_bins'    => ['required', 'numeric'],
            'selected_allocations.*.item'          => ['required', 'array'],
            'selected_allocations.*.item.bin_size' => ['required', 'numeric', Rule::in([500, 1000, 2000])],
        ];

        $allocationIds = Arr::pluck($this->input('selected_allocations', []), ['id']);
        $allocations = AllocationHelper::getAvailableAllocationForCutting(['id' => $allocationIds]);
        $allocations = $allocations->keyBy('id');
        
        foreach ($this->input('selected_allocations', []) as $key => $inputs) {
            $allocation = $allocations[$inputs['id']] ?? null;

            $binsInKg = $allocation->available_no_of_bins * $allocation->item->bin_size;

            if ($this->isMethod('PATCH')) {
                $cutting = Cutting::query()
                    ->with(['items' => fn($query) => $query->where('foreignable_id', $allocation->id)])
                    ->find($this->route('cutting'));
                foreach ($cutting->items as $item) {
                    $binsInKg += $item->no_of_bins * $item->bin_size;
                }
            }

            $allocation->available_no_of_bins = $binsInKg / $allocation->item->bin_size;

            $rules["selected_allocations.{$key}.no_of_bins"] = [
                'required',
                'numeric',
                'gt:0',
                "max:{$allocation->available_no_of_bins}",
            ];
        }

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
            'fungicide.*'                          => 'fungicide',
            'selected_allocations'                 => 'allocation',
            'selected_allocations.*.allocation_id' => 'allocation',
            'selected_allocations.*.no_of_bins'    => 'no of bins to cut',
        ];
    }
}
