<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login_code' => ['required', 'string', 'max:16'],
            'pin' => ['required', 'digits_between:4,6'],
            'device_name' => Concerns::deviceName(),
        ];
    }
}
