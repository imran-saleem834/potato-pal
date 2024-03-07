<?php

namespace App\Http\Requests;

use App\Models\Allocation;
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
            'buyer_id'                                         => ['required', 'numeric', 'exists:users,id'],
            'cut_date'                                         => ['nullable', 'date'],
            'cool_store'                                       => ['nullable', 'array'],
            'cool_store.*'                                     => ['nullable', 'numeric'],
            'comment'                                          => ['nullable', 'string', 'max:255'],
            'fungicide'                                        => ['nullable', 'array'],
            'fungicide.*'                                      => ['nullable', 'numeric'],
            'selected_allocations'                             => ['required', 'array'],
            'selected_allocations.*.id'                        => [
                'nullable',
                'numeric',
                'exists:cutting_allocations,id',
            ],
            'selected_allocations.*.allocation_id'             => ['required', 'numeric', 'exists:allocations,id'],
            'selected_allocations.*.no_of_bins_before_cutting' => ['required', 'numeric'],
            'selected_allocations.*.weight_before_cutting'     => ['required', 'numeric'],
            'selected_allocations.*.no_of_bins_after_cutting'  => ['required', 'numeric'],
            'selected_allocations.*.weight_after_cutting'      => ['required', 'numeric'],
        ];

        foreach ($this->input('selected_allocations', []) as $key => $inputs) {
            $allocation = Allocation::with(['cuttings'])->find($inputs['allocation_id']);

            foreach ($allocation->cuttings as $cutting) {
                if ($cutting->id != ($inputs['id'] ?? 0)) {
                    $allocation->no_of_bins -= $cutting->no_of_bins_before_cutting;
                    $allocation->weight     -= $cutting->weight_before_cutting;
                }
            }

            $rules["selected_allocations.{$key}.no_of_bins_before_cutting"] = [
                'required',
                'numeric',
                'gt:0',
                "max:$allocation->no_of_bins",
            ];
            $rules["selected_allocations.{$key}.weight_before_cutting"]     = [
                'required',
                'numeric',
                'gt:0',
                "max:$allocation->weight",
            ];

            $rules["selected_allocations.{$key}.no_of_bins_after_cutting"] = [
                'required',
                'numeric',
                'gt:0',
                'lte:'.$inputs['no_of_bins_before_cutting'],
            ];
            $rules["selected_allocations.{$key}.weight_after_cutting"]     = [
                'required',
                'numeric',
                'gt:0',
                'lte:'.$inputs['weight_before_cutting'],
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
            'fungicide.*'                                      => 'fungicide',
            'selected_allocations'                             => 'allocation',
            'selected_allocations.*.allocation_id'             => 'allocation',
            'selected_allocations.*.no_of_bins_before_cutting' => 'no of bins to cut',
            'selected_allocations.*.weight_before_cutting'     => 'weight to cut',
            'selected_allocations.*.no_of_bins_after_cutting'  => 'no of bins after cut',
            'selected_allocations.*.weight_after_cutting'      => 'weight after cut',
        ];
    }
}
