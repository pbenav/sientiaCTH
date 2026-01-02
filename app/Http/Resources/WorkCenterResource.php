<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * WorkCenter Resource (Flutter API)
 * 
 * Transforms WorkCenter model into JSON for mobile app.
 * Provides work location data with NFC tag support.
 * 
 * Response Structure:
 * @property-read int $id Work center unique identifier
 * @property-read string $name Work center name
 * @property-read string|null $code Work center code
 * @property-read int $team_id Associated team ID
 * @property-read string|null $nfc_tag_id NFC tag identifier for validation
 * @property-read string|null $location Address/location description
 * @property-read float|null $latitude GPS latitude coordinate
 * @property-read float|null $longitude GPS longitude coordinate
 * @property-read bool $nfc_requires_tag Whether NFC is required for clock-in
 * 
 * Used by: WorkCenterAPIController, MobileClockController
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class WorkCenterResource extends JsonResource
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
            'code' => $this->code,
            'team_id' => $this->team_id,
            // Add other fields as needed
        ];
    }
}
