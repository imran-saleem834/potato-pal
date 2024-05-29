<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use Illuminate\Validation\Rule;
use App\Models\RemainingReceival;
use Illuminate\Foundation\Http\FormRequest;

class AllocationRequest extends FormRequest
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
        $remainingReceivalId = $this->get('select_receival')['remaining_receival_id'];

        $receival = RemainingReceival::query()
            ->select(['unique_key', 'no_of_bins', 'weight'])
            ->find($remainingReceivalId)
            ->toArray();

        $noOfBins = $receival['no_of_bins'] ?? 0;
        $weight   = $receival['weight'] ?? 0;

        $rules = [
            'buyer_id'        => ['required', 'numeric', 'exists:users,id'],
            'grower_id'       => ['required', 'numeric', 'exists:users,id'],
            'unique_key'      => ['required', 'string'],
            'no_of_bins'      => ['required', 'numeric', 'gt:0', "max:$noOfBins"],
            'weight'          => ['required', 'numeric', 'gte:0', "max:$weight"],
            'bin_size'        => ['required', 'numeric', Rule::in([500, 1000, 2000])],
            'paddock'         => ['required', 'string'],
            'comment'         => ['nullable', 'string', 'max:255'],
            'grower_group'    => ['required', 'array', 'max:1'],
            'seed_variety'    => ['required', 'array', 'max:1'],
            'seed_generation' => ['required', 'array', 'max:1'],
            'seed_class'      => ['required', 'array', 'max:1'],
            'seed_type'       => ['required', 'array', 'max:1'],
        ];

        if ($this->isMethod('PATCH')) {
            $allocation = Allocation::select(['id', 'unique_key'])->with(['item'])->find($this->route('allocation'));
            if ($receival['unique_key'] === $allocation->unique_key) {
                $noOfBins = $allocation->item->no_of_bins + $noOfBins;
                $weight   = $allocation->item->weight + $weight;
            }

            $rules['no_of_bins'] = ['required', 'numeric', 'gt:0', "max:$noOfBins"];
            $rules['weight']     = ['required', 'numeric', 'gte:0', "max:$weight"];
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
            'buyer_id'   => 'buyer',
            'grower_id'  => 'grower',
            'unique_key' => 'receival',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'weight.gte'  => 'The :attribute field must be greater than or equal to :value kg.',
            'weight.max'  => 'The :attribute field must not be greater than :max kg.',
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
