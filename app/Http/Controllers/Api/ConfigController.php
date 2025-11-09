<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * API Controller for Flutter app configuration
 * 
 * Provides endpoints for dynamic server configuration,
 * allowing Flutter apps to adapt to different CTH installations
 */
class ConfigController extends Controller
{
    /**
     * Get server configuration for Flutter app
     * 
     * Returns essential server information that allows the Flutter app
     * to configure itself for this specific CTH installation
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServerConfig()
    {
        $config = [
            'server_info' => [
                'name' => config('app.name', 'CTH - Control de Tiempo y Horarios'),
                'version' => '0.0.1',
                'api_version' => 'v1',
                'timezone' => config('app.timezone', 'UTC'),
                'locale' => config('app.locale', 'es'),
            ],
            'endpoints' => [
                'base_url' => url('/'),
                // canonical api base for clients
                'api_base' => url('/api/v1'),
                'auth' => [
                    'mobile_login' => url('/api/v1/mobile/auth'),
                    'mobile_verify' => url('/api/v1/mobile/verify'),
                    'mobile_logout' => url('/api/v1/mobile/logout'),
                ],
                'clock' => [
                    'clock_in' => url('/api/v1/mobile/clock-in'),
                    'clock_out' => url('/api/v1/mobile/clock-out'),
                    'history' => url('/api/v1/mobile/history'),
                    'today' => url('/api/v1/mobile/today'),
                ],
                'nfc' => [
                    'verify_tag' => url('/api/v1/nfc/verify'),
                    'work_centers' => url('/api/v1/mobile/work-centers'),
                ]
            ],
            'features' => [
                'nfc_verification' => true,
                'geolocation_required' => true,
                'multiple_work_centers' => true,
                'break_management' => true,
                'offline_sync' => false, // Future feature
            ],
            'limits' => [
                'max_clock_distance_meters' => 100,
                'session_timeout_minutes' => 480, // 8 hours
                'max_daily_breaks' => 3,
            ],
            'ui_config' => [
                'primary_color' => '#3B82F6', // Blue
                'accent_color' => '#10B981',  // Green
                'error_color' => '#EF4444',   // Red
                'warning_color' => '#F59E0B', // Amber
                'company_logo_url' => url('/images/logo.png'),
                'show_debug_info' => config('app.debug', false),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $config,
            'timestamp' => now()->toISOString(),
            'instructions' => [
                'Save this configuration in your Flutter app',
                'Use the provided endpoints for API calls',
                'Check features.nfc_verification before enabling NFC functionality',
                'Respect the limits configuration for better UX'
            ]
        ]);
    }

    /**
     * Get work centers with NFC configuration
     * 
     * Returns work centers that have NFC tags configured,
     * used by Flutter app to validate NFC reads
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkCentersWithNFC()
    {
        $workCenters = \App\Models\WorkCenter::whereNotNull('nfc_tag_id')
            ->select(['id', 'name', 'code', 'nfc_tag_id', 'nfc_tag_description', 'nfc_payload'])
            ->get()
            ->map(function ($center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'code' => $center->code,
                    'nfc_tag_id' => $center->nfc_tag_id,
                    'nfc_payload' => $center->nfc_payload,
                    'description' => $center->nfc_tag_description,
                    'location_hint' => $center->nfc_tag_description ?: 'NFC tag at ' . $center->name,
                    'has_full_payload' => !empty($center->nfc_payload),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $workCenters,
            'count' => $workCenters->count(),
            'message' => $workCenters->count() > 0 
                ? 'NFC-enabled work centers found' 
                : 'No work centers have NFC tags configured yet'
        ]);
    }

    /**
     * Verify NFC tag against work center
     * 
     * Validates that a scanned NFC tag ID or payload matches a configured work center.
     * Supports both simple NFC ID verification and full payload verification with auto-configuration.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyNFCTag(Request $request)
    {
        $request->validate([
            'nfc_id' => 'required|string',
        ]);

        $nfcData = $request->nfc_id;
        $workCenter = null;
        $configData = null;

        // Intenta parsear como JSON (payload completo)
        $payloadData = json_decode($nfcData, true);
        
        if ($payloadData && is_array($payloadData)) {
            // Es un payload JSON, buscar por payload completo
            $workCenter = \App\Models\WorkCenter::where('nfc_payload', $nfcData)->first();
            
            if ($workCenter) {
                // Determinar URL del servidor desde el payload compacto
                $serverUrl = $payloadData['url'] ?? $payloadData['server_url'] ?? null;
                $apiEndpoint = $serverUrl ? $serverUrl . '/api/v1' : ($payloadData['api_endpoint'] ?? null);
                
                $configData = [
                    'server_configured' => true,
                    'auto_config_data' => $payloadData,
                    'server_url' => $serverUrl,
                    'api_endpoint' => $apiEndpoint,
                ];
            }
        } else {
            // Es un simple NFC ID, buscar por nfc_tag_id
            $workCenter = \App\Models\WorkCenter::where('nfc_tag_id', $nfcData)->first();
        }

        if (!$workCenter) {
            return response()->json([
                'success' => false,
                'error' => 'NFC tag not recognized or not configured',
                'code' => 'NFC_TAG_NOT_FOUND',
                'data' => [
                    'scanned_data' => $nfcData,
                    'is_payload' => $payloadData !== null,
                    'suggestions' => [
                        'Verify the NFC tag is correctly placed',
                        'Check that the work center has NFC configured in the web app',
                        'Contact your administrator to configure this NFC tag'
                    ]
                ]
            ], 404);
        }

        $responseData = [
            'work_center' => [
                'id' => $workCenter->id,
                'name' => $workCenter->name,
                'code' => $workCenter->code,
                'team_id' => $workCenter->team_id,
                'description' => $workCenter->nfc_tag_description,
            ],
            'verification' => [
                'verified_at' => now()->toISOString(),
                'nfc_data' => $nfcData,
                'status' => 'verified'
            ]
        ];

        // Agregar datos de configuración si es un payload completo
        if ($configData) {
            $responseData['auto_configuration'] = $configData;
        }

        return response()->json([
            'success' => true,
            'work_center' => [
                'id' => $workCenter->id,
                'name' => $workCenter->name,
                'code' => $workCenter->code,
                'team_id' => $workCenter->team_id,
                'description' => $workCenter->nfc_tag_description,
            ],
            'message' => $configData 
                ? 'NFC verified and server auto-configuration data provided' 
                : "NFC verification successful for {$workCenter->name}"
        ]);
    }

    /**
     * Test endpoint connectivity
     * 
     * Simple ping endpoint to test if the server is reachable
     * and the API is functioning correctly
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping()
    {
        return response()->json([
            'success' => true,
            'message' => 'CTH API is online and ready',
            'server_time' => now()->toISOString(),
            'api_version' => 'v1',
            'status' => 'healthy'
        ]);
    }
}