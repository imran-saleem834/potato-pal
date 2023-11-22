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
            'name'        => ['required', 'string', 'max:50'],
            'email'       => ['required', 'string', 'email', 'max:90', 'unique:users'],
            'username'    => ['required', 'string', 'max:50', 'unique:users'],
            'phone'       => ['required', 'string', 'max:20'],
            'role'        => ['nullable', 'array'],
            'grower_name' => ['nullable', 'string', 'max:50'],
            'grower_tags' => ['nullable', 'array'],
            'buyer_tags'  => ['nullable', 'array'],
            'paddocks'    => ['nullable', 'array'],
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
}
