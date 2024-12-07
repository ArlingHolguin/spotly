<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UrlRequest extends FormRequest
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
            'original_url' => 'required|url|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'original_url.required' => 'La URL original es obligatoria.',
            'original_url.url' => 'El campo debe ser una URL válida.',
            'original_url.max' => 'La URL no puede exceder los 2048 caracteres.',
        ];
    }

    /**
     * Reglas adicionales de validación.
     */
    protected function prepareForValidation()
    {
        if (Str::startsWith($this->input('original_url'), 'javascript:')) {
            abort(400, 'URL no válida.');
        }

        if ($this->input('honeypot') !== null) {
            abort(400, 'Detección de bot.');
        }
    }
}
