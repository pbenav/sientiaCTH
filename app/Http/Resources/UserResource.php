<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'family_name1' => $this->family_name1,
            'family_name2' => $this->family_name2,
            'email' => $this->email,
            'user_code' => $this->user_code,
            'profile_photo_url' => $this->profile_photo_url,
            'current_team_id' => $this->current_team_id,
            // Include other relevant fields, but avoid sensitive ones like password
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
