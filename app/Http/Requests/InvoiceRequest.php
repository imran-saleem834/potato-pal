<?php

namespace App\Http\Requests;

use App\Models\Grade;
use App\Models\Cutting;
use App\Models\Receival;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            'invoiceable_type' => [
                'required',
                'string',
                Rule::in([Receival::class, Grade::class, Cutting::class])
            ],
            'invoiceable_id'   => ['required', 'numeric'],
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
            'invoiceable_type' => 'invoice type',
        ];
    }
}
