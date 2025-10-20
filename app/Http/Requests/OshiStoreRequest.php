<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OshiStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'school'  => ['nullable', 'string', 'max:100'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'grade'   => ['nullable', 'string', 'max:50'],
            'gender'  => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '名前は必須です。',
        ];
    }
}
