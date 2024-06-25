<?php

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
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
        return [
            'unload_id'           => ['required', 'numeric', 'exists:unloads,id'],
            'category'            => ['required', 'string', Rule::in(Arr::pluck(Grade::CATEGORIES, ['value']))],
            'bins_tipped'         => ['nullable', 'array'],
            'bins_tipped.*'       => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'whole_seed'          => ['nullable', 'array'],
            'whole_seed.*'        => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'oversize'            => ['nullable', 'array'],
            'oversize.*'          => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'round'               => ['nullable', 'array'],
            'round.*'             => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'cut_sets'            => ['nullable', 'array'],
            'cut_sets.*'          => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'waste'               => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'no_of_bulk_bags_out' => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'net_weight_bags_out' => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'fungicide'           => ['nullable', 'boolean'],
            'fungicide_used'      => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'start'               => ['nullable', 'date_format:H:i'],
            'end'                 => ['nullable', 'date_format:H:i'],
            'no_of_crew'          => ['nullable', 'numeric', 'gte:0', 'max:999999'],
            'comments'            => ['nullable', 'string', 'max:191'],
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
            'unload_id' => 'Unload',
        ];
    }
}
