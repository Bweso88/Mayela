<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'roles' => $this->getRoleNames()->values(),
            'student_profile' => $this->when(
                $this->relationLoaded('studentProfile') && $this->studentProfile,
                fn () => StudentProfileResource::make($this->studentProfile),
            ),
            'children' => $this->when(
                $this->relationLoaded('parentProfile') && $this->parentProfile,
                fn () => StudentProfileResource::collection($this->parentProfile->children),
            ),
        ];
    }
}
