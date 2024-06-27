<?php

namespace App\Http\Requests;

use App\Models\Reallocation;
use App\Helpers\AllocationHelper;
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
        $rules = [
            'buyer_id'            => ['required', 'numeric', 'exists:users,id'],
            'allocation_buyer_id' => ['required', 'numeric', 'exists:users,id'],
            'comment'             => ['nullable', 'string', 'max:255'],
            'selected_cutting'    => ['required', 'array'],
            'selected_cutting.id' => ['nullable', 'numeric', 'exists:cuttings,id'],
        ];

        $inputs  = $this->input('selected_cutting', []);
        $cutting = AllocationHelper::getAvailableCuttingsForReallocation(['id' => $inputs['id']])->first();

        if ($this->isMethod('PATCH')) {
            $reallocation = Reallocation::query()
                ->with(['item' => fn ($query) => $query->where('foreignable_id', $cutting->id)])
                ->find($this->route('reallocation'));

            if (! empty($reallocation->item)) {
                $cutting->available_half_tonnes = $cutting->available_half_tonnes + $reallocation->item->half_tonnes;
                $cutting->available_one_tonnes  = $cutting->available_one_tonnes + $reallocation->item->one_tonnes;
                $cutting->available_two_tonnes  = $cutting->available_two_tonnes + $reallocation->item->two_tonnes;
            }
        }

        $rules['half_tonnes'] = ['nullable', 'numeric', "max:{$cutting->available_half_tonnes}"];
        $rules['one_tonnes']  = ['nullable', 'numeric', "max:{$cutting->available_one_tonnes}"];
        $rules['two_tonnes']  = ['nullable', 'numeric', "max:{$cutting->available_two_tonnes}"];

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
            'buyer_id'            => 'reallocate to buyer',
            'allocation_buyer_id' => 'reallocate from buyer',
            'selected_cutting'    => 'cutting',
            'selected_cutting.id' => 'cutting',
        ];
    }
}
