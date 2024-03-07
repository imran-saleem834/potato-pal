<?php

namespace App\Http\Requests;

use App\Models\Dispatch;
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
        $allocation = $this->get('selected_allocation');
        $noOfBins   = (float) ($allocation['no_of_bins'] ?? 0);
        $weight     = (float) ($allocation['weight'] ?? 0);

        $reallocation = $this->get('selected_reallocation');
        if (! empty($reallocation)) {
            $noOfBins = (float) ($reallocation['no_of_bins'] ?? 0);
            $weight   = (float) ($reallocation['weight'] ?? 0);
        }

        $rules = [
            'buyer_id'            => ['required', 'numeric', 'exists:users,id'],
            'allocation_buyer_id' => ['required', 'numeric', 'exists:users,id'],
            'allocation_id'       => [
                'nullable',
                'required_without:reallocation_id',
                'numeric',
                'exists:allocations,id',
            ],
            'reallocation_id'     => [
                'nullable',
                'required_without:allocation_id',
                'numeric',
                'exists:reallocations,id',
            ],
            'no_of_bins'          => ['required', 'numeric', 'gt:0', "max:$noOfBins"],
            'weight'              => ['required', 'numeric', 'gt:0', "max:$weight"],
            'comment'             => ['nullable', 'string', 'max:255'],
        ];

        if ($this->isMethod('PATCH')) {
            $dispatch = Dispatch::find($this->route('dispatch'));

            $noOfBins = $dispatch->no_of_bins + $noOfBins;
            $weight   = $dispatch->weight + $weight;

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
            'buyer_id'            => 'dispatch buyer',
            'allocation_buyer_id' => 'allocation buyer',
            'allocation_id'       => 'allocation',
            'reallocation_id'     => 'reallocation',
        ];
    }
}
