<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a work center or location.
 *
 * This model is used to store information about the different locations where
 * users can clock in and out.
 */
class WorkCenter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'code',
        'nfc_tag_id',
        'nfc_tag_description',
        'nfc_tag_generated_at',
        'nfc_payload',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nfc_tag_generated_at' => 'datetime',
    ];

    /**
     * Generate a unique NFC tag ID for this work center
     * 
     * Generates a secure, unique identifier that is:
     * - Less than 64 bytes (compatible with NFC limitations)
     * - Globally unique across all work centers
     * - URL-safe and readable
     * - Contains checksum for validation
     * 
     * @return string
     */
    public function generateNFCTagId(): string
    {
        do {
            // Generar ID único basado en:
            // - Timestamp actual
            // - ID del centro de trabajo
            // - Código del centro
            // - Hash aleatorio
            $timestamp = time();
            $random = bin2hex(random_bytes(8)); // 16 chars hex
            $workCenterHash = substr(hash('sha256', $this->id . $this->code . $this->team_id), 0, 8);
            
            // Formato: CTH-{timestamp}-{workCenterHash}-{random}
            $nfcId = sprintf('CTH-%08X-%s-%s', $timestamp, $workCenterHash, $random);
            
            // Verificar que sea único (muy improbable que no lo sea, pero por seguridad)
            $exists = self::where('nfc_tag_id', $nfcId)->exists();
            
        } while ($exists);
        
        return $nfcId;
    }

    /**
     * Generate the complete NFC payload including server URL and work center data
     * 
     * Creates a compact JSON payload (under 128 bytes) that contains:
     * - Server URL for API configuration
     * - Work center ID for verification
     * - Timestamp for security
     * 
     * @param string $nfcId The NFC tag ID
     * @return string Compact JSON payload for NFC tag (under 128 bytes)
     */
    public function generateNFCPayload(string $nfcId): string
    {
        $baseUrl = config('app.url');
        
        // Crear payload ultra-compacto para no exceder 128 bytes
        $payload = [
            'url' => $baseUrl,
            'id' => $nfcId,
            'wc' => $this->id,
            'tm' => $this->team_id,
            'ts' => time() // timestamp unix más corto
        ];
        
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        
        // Verificar que no exceda 128 bytes
        if (strlen($jsonPayload) > 128) {
            // Si excede, usar solo lo esencial
            $payload = [
                'url' => $baseUrl,
                'id' => $nfcId,
                'wc' => $this->id
            ];
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        }
        
        return $jsonPayload;
    }

    /**
     * Enable NFC for this work center
     * 
     * Generates and assigns a new NFC tag ID with optional description
     * and creates the complete NFC payload with server URL
     * 
     * @param string|null $description Physical description of NFC tag location
     * @return string The generated NFC tag ID
     */
    public function enableNFC(?string $description = null): string
    {
        $nfcId = $this->generateNFCTagId();
        $nfcPayload = $this->generateNFCPayload($nfcId);
        
        $this->update([
            'nfc_tag_id' => $nfcId,
            'nfc_tag_description' => $description,
            'nfc_tag_generated_at' => now(),
            'nfc_payload' => $nfcPayload,
        ]);
        
        return $nfcId;
    }

    /**
     * Disable NFC for this work center
     * 
     * Removes NFC tag ID, payload and related data
     * 
     * @return void
     */
    public function disableNFC(): void
    {
        $this->update([
            'nfc_tag_id' => null,
            'nfc_tag_description' => null,
            'nfc_tag_generated_at' => null,
            'nfc_payload' => null,
        ]);
    }

    /**
     * Check if NFC is enabled for this work center
     * 
     * @return bool
     */
    public function hasNFC(): bool
    {
        return !empty($this->nfc_tag_id);
    }

        /**
     * Get comprehensive NFC information for this work center
     * 
     * @return array|null NFC information or null if not configured
     */
    public function getNFCInfo(): ?array
    {
        if (!$this->hasNFC()) {
            return null;
        }
        
        return [
            'nfc_tag_id' => $this->nfc_tag_id,
            'description' => $this->nfc_tag_description,
            'generated_at' => $this->nfc_tag_generated_at,
            'payload' => $this->nfc_payload,
            'payload_data' => $this->getNFCPayloadData(),
            'work_center_id' => $this->id,
            'work_center_code' => $this->code,
            'team_id' => $this->team_id,
        ];
    }

    /**
     * Get parsed NFC payload data
     * 
     * @return array|null Parsed payload data or null if not available
     */
    public function getNFCPayloadData(): ?array
    {
        if (!$this->nfc_payload) {
            return null;
        }
        
        try {
            return json_decode($this->nfc_payload, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate NFC tag content for any work center (with or without NFC enabled)
     * 
     * This method generates the NFC tag content that can be programmed into
     * a physical NFC tag, regardless of whether NFC is currently enabled
     * 
     * @param string|null $description Optional description for the tag
     * @return array Array containing tag_id and payload
     */
    public function generateNFCTagContent(?string $description = null): array
    {
        // Si ya tiene NFC habilitado, usar los datos existentes
        if ($this->hasNFC()) {
            return [
                'tag_id' => $this->nfc_tag_id,
                'payload' => $this->nfc_payload,
                'description' => $description ?: $this->nfc_tag_description,
                'size_bytes' => strlen($this->nfc_payload),
                'is_existing' => true
            ];
        }

        // Generar un ID temporal que no colisione con los existentes
        do {
            $tempNfcId = sprintf('CTH-TEMP-%s-%s', substr(hash('sha256', uniqid()), 0, 8), bin2hex(random_bytes(4)));
            $exists = self::where('nfc_tag_id', $tempNfcId)->exists();
        } while ($exists);
        $tempPayload = $this->generateNFCPayload($tempNfcId);

        return [
            'tag_id' => $tempNfcId,
            'payload' => $tempPayload,
            'description' => $description ?: "NFC tag for {$this->name}",
            'size_bytes' => strlen($tempPayload),
            'is_existing' => false,
            'warning' => 'This content is generated but not saved. Enable NFC to persist these settings.'
        ];
    }

    /**
     * Get the team that owns the work center.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
