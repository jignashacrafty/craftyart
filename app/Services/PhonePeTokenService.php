<?php

namespace App\Services;

use App\Models\PhonePeToken;
use App\Models\Pricing\PaymentConfiguration;
use App\Enums\PaymentGatewayEnum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PhonePeTokenService
{
    protected $clientId;
    protected $clientSecret;
    protected $clientVersion;
    protected $production;

    public function __construct()
    {
        $config = PaymentConfiguration::whereGateway(PaymentGatewayEnum::PHONEPE->value)
            ->first();

        if ($config) {
            $credentials = $config->credentials;
            $this->clientId = $credentials['client_id'] ?? '';
            $this->clientSecret = $credentials['client_secret'] ?? '';
            $this->clientVersion = $credentials['client_version'] ?? '';

            // Get environment setting
            if (isset($credentials['environment'])) {
                $this->production = ($credentials['environment'] === 'production');
            } else {
                $this->production = false; // Default to sandbox
            }
        }
    }

    /**
     * Get or generate access token with caching
     */
    public function getAccessToken(): string
    {
        // Check cache first
        $cachedToken = Cache::get('phonepe_access_token');
        if ($cachedToken) {
            Log::info('✅ Using cached PhonePe token');
            return $cachedToken;
        }

        // Check database for valid token
        $tokenRecord = PhonePeToken::getActiveToken();
        if ($tokenRecord && !$tokenRecord->isExpiringSoon()) {
            $cacheMinutes = now()->diffInMinutes($tokenRecord->expires_at) - 5;
            Cache::put('phonepe_access_token', $tokenRecord->access_token, $cacheMinutes * 60);

            Log::info('✅ Using database PhonePe token', [
                'token_id' => $tokenRecord->id,
                'expires_at' => $tokenRecord->expires_at
            ]);

            return $tokenRecord->access_token;
        }

        // Generate new token
        return $this->generateNewToken();
    }

    /**
     * Generate new access token
     */
    protected function generateNewToken(): string
    {
        // Use correct OAuth URL based on environment
        $url = $this->production
            ? 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token';

        try {
            Log::info('🔄 Generating new PhonePe access token', [
                'environment' => $this->production ? 'production' : 'sandbox',
                'url' => $url
            ]);

            $response = Http::asForm()->post($url, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'client_version' => $this->clientVersion,
                'grant_type' => 'client_credentials',
            ]);

            $data = $response->json();

            if (!isset($data['access_token'])) {
                Log::error('❌ PhonePe OAuth failed', ['response' => $data]);
                throw new \Exception('PhonePe OAuth failed: ' . json_encode($data));
            }

            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;
            $expiresAt = now()->addSeconds($expiresIn);

            // Mark old tokens as expired
            PhonePeToken::where('status', 'active')->update(['status' => 'expired']);

            // Create new token record
            $tokenRecord = PhonePeToken::create([
                'access_token' => $accessToken,
                'expires_in' => $expiresIn,
                'expires_at' => $expiresAt,
                'token_type' => $data['token_type'] ?? 'Bearer',
                'status' => 'active',
                'metadata' => $data
            ]);

            // Cache token
            $cacheMinutes = $expiresIn / 60 - 5;
            Cache::put('phonepe_access_token', $accessToken, $cacheMinutes * 60);

            Log::info('✅ New PhonePe token generated', [
                'token_id' => $tokenRecord->id,
                'expires_at' => $expiresAt
            ]);

            return $accessToken;

        } catch (\Exception $e) {
            Log::error('❌ PhonePe token generation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Refresh token manually
     */
    public function refreshToken(): string
    {
        Cache::forget('phonepe_access_token');
        return $this->generateNewToken();
    }
}
