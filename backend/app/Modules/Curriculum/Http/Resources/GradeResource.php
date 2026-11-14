<?php

namespace App\Modules\Curriculum\Http\Resources;

use App\Modules\Curriculum\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Grade */
class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'universe' => $this->universe->value,
            'stage' => $this->whenLoaded('stage', fn () => ['code' => $this->stage->code, 'name' => $this->stage->name]),
            'cycle' => $this->whenLoaded('cycle', fn () => $this->cycle
                ? ['code' => $this->cycle->code, 'name' => $this->cycle->name]
                : null),
            'tracks' => TrackResource::collection($this->whenLoaded('tracks')),
        ];
    }
}
