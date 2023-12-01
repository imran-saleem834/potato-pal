<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Laravel\Fortify\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name'                => ['required', 'string', 'max:50'],
            'email'               => ['required', 'string', 'email', 'max:90', 'unique:users'],
            'username'            => ['required', 'string', 'max:50', 'unique:users'],
            'phone'               => ['required', 'string', 'max:20'],
            'role'                => ['nullable', 'array'],
            'grower'              => ['nullable', 'array'],
            'grower_name'         => ['nullable', 'string', 'max:50'],
            'grower_tags'         => ['nullable', 'array'],
            'buyer'               => ['nullable', 'array'],
            'buyer_tags'          => ['nullable', 'array'],
            'paddocks'            => ['nullable', 'array'],
            'paddocks.*.name'     => ['required', 'string', 'max:50'],
            'paddocks.*.hectares' => ['required', 'string', 'max:20'],
        ];

        if (!$this->isMethod('POST')) {
            $rules['email']    = [
                'required',
                'string',
                'email',
                'max:90',
                Rule::unique('users')->ignore($this->route('user'))
            ];
            $rules['username'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($this->route('user'))
            ];
        }

        if ($this->isMethod('POST')) {
            $rules['password'] = ['required', 'string', new Password, 'confirmed'];
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $paddocks = $this->input('paddocks');
        foreach ($this->input('paddocks') as $index => $paddock) {
            if (empty($paddock['name']) && empty($paddock['hectares'])) {
                unset($paddocks[$index]);
            }
        }
        $this->merge(['paddocks' => $paddocks]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'paddocks.*.name'     => 'paddock name',
            'paddocks.*.hectares' => 'paddock hectares',
            'buyer'               => 'buyer group',
            'grower'              => 'grower group',
        ];
    }
}
