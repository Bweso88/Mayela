<?php

namespace App\Modules\Curriculum\Http\Resources;

use App\Modules\Curriculum\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Track */
class TrackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'kind' => $this->kind->value,
        ];
    }
}
