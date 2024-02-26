<?php

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LabelRequest extends FormRequest
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
        //        labelable_type: props.label.labelable_type,
        //  labelable_id: props.label.labelable_id,
        //  grower_id: props.label.grower_id,
        //  paddock: props.label.paddock,
        //  receival_id: props.label.receival_id,
        //  type: props.label.type,
        //  comments: props.label.comments,

        return [
            'labelable_type' => ['required', 'numeric', 'exists:unloads,id'],
            'labelable_id'   => ['required', 'numeric', 'exists:unloads,id'],
            'grower_id'      => ['required', 'numeric', 'exists:users,id'],
            'paddock'        => ['required', 'numeric', 'exists:unloads,id'],
            'receival_id'    => ['nullable', 'numeric', 'exists:receivals,id'],
            'type'           => ['required', 'string', Rule::in(Arr::pluck(Grade::CATEGORIES, ['value']))],
            'comments'       => ['nullable', 'string', 'max:191'],

            'unload_id'           => ['required', 'numeric', 'exists:unloads,id'],
            'category'            => ['required', 'string', Rule::in(Arr::pluck(Grade::CATEGORIES, ['value']))],
            'bins_tipped'         => ['nullable', 'array'],
            'bins_tipped.*'       => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'whole_seed'          => ['nullable', 'array'],
            'whole_seed.*'        => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'oversize'            => ['nullable', 'array'],
            'oversize.*'          => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'round'               => ['nullable', 'array'],
            'round.*'             => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'cut_sets'            => ['nullable', 'array'],
            'cut_sets.*'          => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'waste'               => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'no_of_bulk_bags_out' => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'net_weight_bags_out' => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'fungicide'           => ['nullable', 'boolean'],
            'fungicide_used'      => ['nullable', 'numeric', 'gte:0', "max:999999"],
            'start'               => ['nullable', 'date_format:h:i'],
            'end'                 => ['nullable', 'date_format:h:i'],
            'no_of_crew'          => ['nullable', 'numeric', 'gte:0', "max:999999"],
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
