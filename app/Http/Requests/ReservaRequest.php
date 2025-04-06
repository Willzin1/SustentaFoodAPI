<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReservaRequest extends FormRequest
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
            'data' => 'required',
            'hora' => 'required',
            'quantidade_cadeiras' => 'required',
            'quantidade_custom' => 'required_if:quantidade_cadeiras,mais'
        ];
    }

    public function messages(): array
    {
        return [
            'data.required' => 'Informe uma data',
            'hora.required' => 'Informe o horário da reserva',
            'quantidade_cadeiras.required' => 'Informe a quantidade de cadeiras',
            'quantidade_custom.required_if' => 'Informe uma quantidade personalizada'
        ];
    }
}
