<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Curriculum\Http\Resources\GradeResource;
use App\Modules\Curriculum\Http\Resources\TrackResource;
use App\Modules\Identity\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentProfile */
class StudentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birth_date' => $this->birth_date->toDateString(),
            'country_code' => $this->country?->code,
            'school_name' => $this->school_name,
            'grade' => $this->whenLoaded('grade', fn () => $this->grade ? GradeResource::make($this->grade) : null),
            'track' => $this->whenLoaded('track', fn () => $this->track ? TrackResource::make($this->track) : null),
            // Code de connexion de l'enfant : visible par lui-même et ses parents uniquement
            // (la ressource n'est servie qu'à eux, cf. StudentProfilePolicy).
            'login_code' => $this->login_code,
            'level' => $this->level,
            'xp' => $this->xp,
            'avatar' => $this->avatar,
            'requires_parental_consent' => $this->isMinorForConsent(),
        ];
    }
}
