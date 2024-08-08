<?php

namespace App\Http\Requests;

use App\Models\Cutting;
use Illuminate\Validation\Rule;
use App\Helpers\AllocationHelper;
use Illuminate\Validation\Validator;
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
            'buyer_id'            => ['required', 'numeric', 'exists:users,id'],
            'type'                => ['required', 'string', Rule::in(['allocation', 'sizing'])],
            'half_tonnes'         => ['nullable', 'numeric'],
            'one_tonnes'          => ['nullable', 'numeric'],
            'two_tonnes'          => ['nullable', 'numeric'],
            'cut_date'            => ['nullable', 'date'],
            'cool_store'          => ['nullable', 'array'],
            'cool_store.*'        => ['nullable', 'numeric'],
            'fungicide'           => ['nullable', 'array'],
            'fungicide.*'         => ['nullable', 'numeric'],
            'comment'             => ['nullable', 'string', 'max:255'],
            'selected_allocation' => ['required', 'array'],
        ];

        $inputs = $this->input('selected_allocation', []);

        if ($this->input('type') === 'allocation') {
            $rules['selected_allocation.id'] = ['nullable', 'numeric', 'exists:allocations,id'];
            $model = AllocationHelper::getAvailableAllocation(['id' => $inputs['id']])->first();
        } else {
            $rules['selected_allocation.id'] = ['nullable', 'numeric', 'exists:allocation_items,id'];
            $model = AllocationHelper::getAvailableSizing(['id' => $inputs['id']])->first();
        }

        if ($this->isMethod('PATCH')) {
            $cutting = Cutting::query()
                ->with(['item' => fn($query) => $query->where('foreignable_id', $model->id)])
                ->find($this->route('cutting'));

            if (!empty($cutting->item)) {
                $model->available_half_tonnes = $model->available_half_tonnes + $cutting->item->from_half_tonnes;
                $model->available_one_tonnes  = $model->available_one_tonnes + $cutting->item->from_one_tonnes;
                $model->available_two_tonnes  = $model->available_two_tonnes + $cutting->item->from_two_tonnes;
            }
        }

        $rules['from_half_tonnes']       = ['nullable', 'numeric', "max:{$model->available_half_tonnes}"];
        $rules['from_one_tonnes']        = ['nullable', 'numeric', "max:{$model->available_one_tonnes}"];
        $rules['from_two_tonnes']        = ['nullable', 'numeric', "max:{$model->available_two_tonnes}"];
        
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
            'fungicide.*'            => 'fungicide',
            'cool_store.*'           => 'cool store',
            'from_half_tonnes'       => 'half tonnes',
            'from_one_tonnes'        => 'one tonnes',
            'from_two_tonnes'        => 'two tonnes',
            'selected_allocation'    => 'allocation',
            'selected_allocation.id' => 'allocation',
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        $binsInKgFrom =
            $this->input('from_half_tonnes', 0) * 500 +
            $this->input('from_one_tonnes', 0) * 1000 +
            $this->input('from_two_tonnes', 0) * 2000;
        $binsInKgTo =
            $this->input('half_tonnes', 0) * 500 +
            $this->input('one_tonnes', 0) * 1000 +
            $this->input('two_tonnes', 0) * 2000;

        return [
            function (Validator $validator) use ($binsInKgFrom, $binsInKgTo) {
                if ($binsInKgTo > $binsInKgFrom) {
                    $validator->errors()->add('half_tonnes', 'Cut into should be less then or equal to bins tipped.');
                }
            }
        ];
    }
}
