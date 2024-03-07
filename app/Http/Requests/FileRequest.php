<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:191'],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,gif,svg,pdf', 'max:2048'],
        ];

        if ($this->isMethod('POST')) {
            $rules['image'] = ['required', 'mimes:jpeg,png,jpg,gif,svg,pdf', 'max:2048'];
        }

        return $rules;
    }
}
