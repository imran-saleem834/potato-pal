<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Support\Arr;
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
            'email'               => ['nullable', 'string', 'email', 'max:90'],
            'username'            => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'access'              => ['nullable', 'array'],
            'grower_name'         => ['nullable', 'string', 'max:50'],
            'grower_tags'         => ['nullable', 'array'],
            'buyer_name'          => ['nullable', 'string', 'max:50'],
            'buyer_tags'          => ['nullable', 'array'],
            'paddocks'            => ['nullable', 'array'],
            'paddocks.*.name'     => ['required', 'string', 'max:50'],
            'paddocks.*.hectares' => ['required', 'string', 'max:20'],
            'paddocks.*.address'  => ['nullable', 'string', 'max:255'],
            'paddocks.*.gps'      => ['nullable', 'string', 'max:255'],
        ];

        if (in_array('buyer', $this->input('access', []))) {
            $rules = array_merge($rules, ['buyer_name' => ['required', 'string', 'max:50']]);
        }

        if (in_array('grower', $this->input('access', []))) {
            $rules = array_merge($rules, ['grower_name' => ['required', 'string', 'max:50']]);
        }

        $rules = array_merge($rules, Arr::map(User::CATEGORY_INPUTS, function ($input) {
            return [$input => ['nullable', 'array']];
        }));

        if (! $this->isMethod('POST')) {
            $rules['email']    = [
                'nullable',
                'string',
                'email',
                'max:90',
                Rule::unique('users')->ignore($this->route('user')),
            ];
            $rules['username'] = [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                Rule::unique('users')->ignore($this->route('user')),
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
            if (empty($paddock['name']) && empty($paddock['hectares']) && empty($paddock['address']) && empty($paddock['gps'])) {
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
            'paddocks.*.address'  => 'paddock address',
            'paddocks.*.gps'      => 'paddock gps',
        ];
    }
}
