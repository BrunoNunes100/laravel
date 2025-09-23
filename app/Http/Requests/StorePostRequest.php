<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'picture.image' => 'O arquivo deve ser uma imagem.',
            'picture.mimes' => 'A imagem deve estar em um dos formatos: jpeg, png, jpg, gif, webp.',
            'picture.max' => 'A imagem não pode ser maior que 10MB.',
        ];
    }
}
