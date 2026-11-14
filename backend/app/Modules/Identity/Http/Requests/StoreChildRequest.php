<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChildRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'birth_date' => Concerns::birthDate(),
            'country_code' => Concerns::countryCode(),
            'school_name' => ['nullable', 'string', 'max:190'],
            'pin' => ['required', 'digits_between:4,6', 'confirmed'],
            // Consentement explicite du parent pour la création du compte de l'enfant.
            'parental_consent' => ['accepted'],
            // Classe facultative à la création (modifiable ensuite).
            'grade_id' => ['nullable', 'integer'],
            'track_id' => ['nullable', 'integer'],
        ];
    }
}
