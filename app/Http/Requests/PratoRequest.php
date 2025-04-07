<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PratoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->all());

        throw new HttpResponseException(response()->json([
            'message' => 'Erro de validação',
            'errors' => $errors->values(),
        ], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string|max:255',
            'imagem' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'categoria' => 'required|in:Entradas,Prato principal,Sobremesas,Cardapio infantil,Bebidas',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Nome do prato é obrigatório',
            'nome.max' => 'Máximo de :max caracteres',
            'descricao.required' => 'Descrição do prato é obrigatório',
            'descricao.max' => 'Máximo de 255 caracteres',
            'imagem.image' => 'Somente fotos são válidas',
            'imagem.mimes' => 'Somente formatos jpeg, png, jpg, gif',
            'imagem.max' => 'Tamanho máximo aceito :max KB',
            'categoria.required' => 'Selecione uma categoria',
            'categoria.in' => 'Categoria não existe'
        ];
    }
}
