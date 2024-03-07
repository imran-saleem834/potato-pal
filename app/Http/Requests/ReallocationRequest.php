<?php

namespace App\Http\Requests;

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
        $allocation = $this->get('selected_allocation');
        $noOfBins   = (float) ($allocation['no_of_bins'] ?? 0);
        $weight     = (float) ($allocation['weight'] ?? 0);

        $rules = [
            'buyer_id'            => ['required', 'numeric', 'exists:users,id'],
            'allocation_buyer_id' => ['required', 'numeric', 'exists:users,id'],
            'allocation_id'       => ['required', 'numeric', 'exists:allocations,id'],
            'no_of_bins'          => ['required', 'numeric', 'gt:0', "max:$noOfBins"],
            'weight'              => ['required', 'numeric', 'gt:0', "max:$weight"],
            'comment'             => ['nullable', 'string', 'max:255'],
        ];

        if ($this->isMethod('PATCH')) {
            $reallocation = Reallocation::find($this->route('reallocation'));

            $noOfBins = $reallocation->no_of_bins + $noOfBins;
            $weight   = $reallocation->weight + $weight;

            $rules['no_of_bins'] = ['required', 'numeric', 'gt:0', "max:$noOfBins"];
            $rules['weight']     = ['required', 'numeric', 'gt:0', "max:$weight"];
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
            'buyer_id'            => 'reallocation buyer',
            'allocation_buyer_id' => 'allocation buyer',
            'allocation_id'       => 'allocation',
        ];
    }
}
