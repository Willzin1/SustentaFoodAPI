<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|phone:br',
            'password' => 'required|min:6|max:20'
        ];
    }

    public function messages() {
        return [
            'name.required' => 'Nome é obrigatório',
            'email.required' => 'E-mail é obrigatório',
            'email.email' => 'E-mail inválido',
            'email.unique' => 'E-mail já cadastrado',
            'phone.required' => 'Telefone é obrigatório',
            'phone.phone' => 'Insira um telefone válido',
            'password.required' => 'Senha é obrigatório',
            'password.min' => 'Senha precisa conter :min caracteres',
            'password.max' => 'Senha não pode passar de :max caracteres'
        ];
    }
}
