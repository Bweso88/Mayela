<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterParentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Concerns::normalizeEmail($this->input('email'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'required_without:email', 'regex:'.Concerns::PHONE_REGEX, 'unique:users,phone'],
            'password' => Concerns::password(),
            'device_name' => Concerns::deviceName(),
            'accept_terms' => ['accepted'],
        ];
    }
}
