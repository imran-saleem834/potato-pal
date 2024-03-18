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
            'buyer_id'                             => ['required', 'numeric', 'exists:users,id'],
            'half_tonnes'                          => ['nullable', 'required_without_all:one_tonnes,two_tonnes', 'numeric'],
            'one_tonnes'                           => ['nullable', 'required_without_all:half_tonnes,two_tonnes', 'numeric'],
            'two_tonnes'                           => ['nullable', 'required_without_all:half_tonnes,half_tonnes', 'numeric'],
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
                'exists:cutting_allocations,id',
            ],
            'selected_allocations.*.allocation_id' => ['required', 'numeric', 'exists:allocations,id'],
            'selected_allocations.*.no_of_bins'    => ['required', 'numeric'],
        ];

        foreach ($this->input('selected_allocations', []) as $key => $inputs) {
            $allocation = Allocation::with(['cuttings'])->find($inputs['allocation_id']);

            foreach ($allocation->cuttings as $cutting) {
                if ($cutting->id != ($inputs['id'] ?? 0)) {
                    $allocation->no_of_bins -= $cutting->no_of_bins;
                }
            }

            $rules["selected_allocations.{$key}.no_of_bins"] = [
                'required',
                'numeric',
                'gt:0',
                "max:$allocation->no_of_bins",
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
