<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Inscription autonome d'un élève ayant l'âge de la majorité numérique. */
class RegisterStudentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Concerns::normalizeEmail($this->input('email'))]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'birth_date' => Concerns::birthDate(),
            'country_code' => Concerns::countryCode(),
            'school_name' => ['nullable', 'string', 'max:190'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'required_without:email', 'regex:'.Concerns::PHONE_REGEX, 'unique:users,phone'],
            'password' => Concerns::password(),
            'device_name' => Concerns::deviceName(),
            'accept_terms' => ['accepted'],
        ];
    }
}
