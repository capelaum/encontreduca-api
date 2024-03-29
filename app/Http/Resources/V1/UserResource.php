<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\EducationalResource\ResourceVoteCollection;
use App\Models\ResourceVote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'avatarUrl' => $this->avatar_url,
            'reviewCount' => $this->reviews()->count(),
            'resourcesIds' => $this->savedResources()->pluck('id')->toArray(),
            'votes' => new ResourceVoteCollection($this->votes),
        ];
    }
}
