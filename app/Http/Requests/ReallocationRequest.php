<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use App\Models\Reallocation;
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
        $allocation = Allocation::query()
            ->select(['no_of_bins', 'weight'])
            ->find($this->get('allocation_id'))
            ->toArray();

        $noOfBins = $allocation['no_of_bins'] ?? 0;
        $weight   = $allocation['weight'] ?? 0;

        if ($this->isMethod('PATCH')) {
            $reallocation = Reallocation::select(['no_of_bins', 'weight'])->find($this->route('reallocation'));

            $noOfBins = $reallocation->no_of_bins + $noOfBins;
            $weight   = $reallocation->weight + $weight;
        }

        return [
            'buyer_id'            => ['required', 'numeric', 'exists:users,id'],
            'allocation_buyer_id' => ['required', 'numeric', 'exists:users,id'],
            'allocation_id'       => ['required', 'numeric', 'exists:allocations,id'],
            'no_of_bins'          => ['required', 'numeric', 'gt:0', "max:$noOfBins"],
            'weight'              => ['required', 'numeric', 'gt:0', "max:$weight"],
            'comment'             => ['nullable', 'string', 'max:255'],
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
            'buyer_id'            => 'reallocation buyer',
            'allocation_buyer_id' => 'allocation buyer',
            'allocation_id'       => 'allocation',
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
