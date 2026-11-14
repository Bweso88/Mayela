<?php

namespace App\Modules\Curriculum\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'integer'],
            'track_id' => ['nullable', 'integer'],
        ];
    }
}
