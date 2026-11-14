<?php

namespace App\Modules\Curriculum\Http\Resources;

use App\Modules\Curriculum\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Subject */
class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
            'published_chapters_count' => $this->whenCounted('chapters'),
        ];
    }
}
