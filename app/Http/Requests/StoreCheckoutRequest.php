<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'exists:plans,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'document' => ['required', 'string', 'max:14', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/', 'unique:users,cpf'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9()\-\s]{10,20}$/', 'unique:users,phone'],
            'business_name' => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'O plano é obrigatório.',
            'plan_id.exists' => 'O plano selecionado é inválido.',
            'first_name.required' => 'O primeiro nome é obrigatório.',
            'first_name.max' => 'O primeiro nome deve ter no máximo :max caracteres.',
            'last_name.required' => 'O sobrenome é obrigatório.',
            'last_name.max' => 'O sobrenome deve ter no máximo :max caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail deve ter no máximo :max caracteres.',
            'email.unique' => 'Este e-mail já está em uso.',
            'document.required' => 'O CPF é obrigatório.',
            'document.max' => 'O CPF deve ter no máximo :max caracteres.',
            'document.regex' => 'Informe um CPF válido.',
            'document.unique' => 'Este CPF já está em uso.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.max' => 'O telefone deve ter no máximo :max caracteres.',
            'phone.regex' => 'Informe um telefone válido.',
            'phone.unique' => 'Este telefone já está em uso.',
            'business_name.required' => 'O nome do negócio é obrigatório.',
            'business_name.max' => 'O nome do negócio deve ter no máximo :max caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ];
    }
}
