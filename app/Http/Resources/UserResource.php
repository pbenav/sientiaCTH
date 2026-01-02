<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource (Flutter API)
 * 
 * Transforms User model into JSON format for mobile app.
 * Excludes sensitive fields (password, 2FA secrets) for security.
 * 
 * Response Structure:
 * @property-read int $id User unique identifier
 * @property-read string $name User full name
 * @property-read string|null $family_name1 First surname
 * @property-read string|null $family_name2 Second surname
 * @property-read string $email User email address
 * @property-read string|null $user_code Auto-generated user code
 * @property-read string $profile_photo_url Profile photo URL
 * @property-read int|null $current_team_id Active team ID
 * @property-read string $created_at Registration datetime
 * @property-read string $updated_at Last update datetime
 * 
 * Security: Excludes password, two_factor_secret, remember_token
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
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
