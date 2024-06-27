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
            'buyer_id'               => ['required', 'numeric', 'exists:users,id'],
            'type'                   => ['required', 'string', Rule::in(['allocation', 'cutting', 'reallocation'])],
            'selected_allocation.id' => ['nullable', 'numeric'],
            'created_at'             => ['nullable', 'date_format:Y-m-d H:i:s'],
            'buyer_group'            => ['nullable', 'array', 'max:1'],
            'transport'              => ['nullable', 'array', 'max:1'],
            'docket_no'              => ['nullable', 'string', 'max:50'],
            'comment'                => ['nullable', 'string', 'max:255'],
        ];

        $inputs     = $this->input('selected_allocation', []);
        $allocation = AllocationHelper::getAvailableAllocationForDispatch([$inputs['type'].'_id' => $inputs['id']])
            ->where('type', $inputs['type'])
            ->first();

        if ($this->isMethod('PATCH')) {
            $dispatch = Dispatch::query()
                ->with(['item' => fn ($query) => $query->where('foreignable_id', $allocation->id)])
                ->find($this->route('dispatch'));

            if (! empty($dispatch->item)) {
                $allocation->available_half_tonnes = $allocation->available_half_tonnes + $dispatch->item->half_tonnes;
                $allocation->available_one_tonnes  = $allocation->available_one_tonnes + $dispatch->item->one_tonnes;
                $allocation->available_two_tonnes  = $allocation->available_two_tonnes + $dispatch->item->two_tonnes;
            }
        }

        $rules['half_tonnes'] = ['nullable', 'numeric', "max:{$allocation->available_half_tonnes}"];
        $rules['one_tonnes']  = ['nullable', 'numeric', "max:{$allocation->available_one_tonnes}"];
        $rules['two_tonnes']  = ['nullable', 'numeric', "max:{$allocation->available_two_tonnes}"];

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
            'buyer_id'               => 'buyer',
            'selected_allocation'    => 'selection',
            'selected_allocation.id' => 'selection',
        ];
    }
}
