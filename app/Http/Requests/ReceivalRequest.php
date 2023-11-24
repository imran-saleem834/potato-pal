<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Laravel\Fortify\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class ReceivalRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'tia_sample_id' => ['nullable', 'string', 'max:10'],
            'transport' => ['nullable', 'string', 'max:150'],
            'grower_docket_no' => ['nullable', 'string', 'max:50'],
            'chc_receival_docket_no' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:80'],
            'comments' => ['nullable', 'string', 'max:255'],
        ];
    }
}
