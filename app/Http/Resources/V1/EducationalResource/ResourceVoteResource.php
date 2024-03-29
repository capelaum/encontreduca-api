<?php

namespace App\Http\Resources\V1\EducationalResource;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceVoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'resourceId' => $this->resource_id,
            'vote' => $this->vote,
            'justification' => $this->justification,
        ];
    }
}
