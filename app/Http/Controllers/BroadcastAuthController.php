<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    // Remove the constructor or fix dependency injection
    public function authenticate(Request $request): JsonResponse
    {
        try {
            Log::info('Broadcast auth request received', [
                'headers' => $request->headers->all(),
                'all_data' => $request->all()
            ]);

            // Get user ID from custom header
            $userId = $request->header('X-User-Id');

            if (!$userId) {
                Log::warning('Missing X-User-Id header');
                return response()->json(['error' => 'User ID required'], 403);
            }

            $socketId = $request->input('socket_id');
            $channelName = $request->input('channel_name');

            if (!$socketId || !$channelName) {
                Log::warning('Missing socket_id or channel_name', [
                    'socket_id' => $socketId,
                    'channel_name' => $channelName
                ]);
                return response()->json(['error' => 'Socket ID and channel name required'], 403);
            }

            // Validate channel access
            if (!$this->validateChannelAccess($userId, $channelName)) {
                Log::warning('Channel access denied', [
                    'user_id' => $userId,
                    'channel_name' => $channelName
                ]);
                return response()->json(['error' => 'Channel access denied'], 403);
            }

            // Generate authentication response
            $authResponse = $this->generateAuthResponse($socketId, $channelName);

            Log::info('Broadcast auth successful', [
                'user_id' => $userId,
                'channel_name' => $channelName
            ]);

            return response()->json($authResponse);
        } catch (\Exception $e) {
            Log::error('Broadcast auth error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }

    protected function validateChannelAccess($userId, $channelName): bool
    {
        // Only allow private channels
        if (!str_starts_with($channelName, 'private-')) {
            return false;
        }

        $channelParts = explode('-', $channelName, 2);
        Log::info("Channel Parts ".json_encode($channelParts));
        $channelType = $channelParts[1] ?? '';

        return $this->validatePrivateChannel($userId, $channelType);
    }

    protected function validatePrivateChannel($userId, $channelType): bool
    {
        Log::info("Channel Type", ['channelType' => $channelType]);

        if (str_starts_with($channelType, 'user-')) {
            $channelUserId = substr($channelType, strlen('user-'));

            Log::info('Validating user channel', [
                'user_id' => $userId,
                'channel_user_id' => $channelUserId
            ]);

            return (string) $userId === (string) $channelUserId;
        }

        if (str_starts_with($channelType, 'session-')) {
            $email = substr($channelType, strlen('session-'));

            return $this->validateSessionChannel($userId, $email);
        }

        return false;
    }

//    protected function validatePrivateChannel($userId, $channelType): bool
//    {
//        Log::info("Channel Type ".json_encode($channelType));
//        $channelParts = explode('-', $channelType);
//        Log::info("Channel Parts 2 ".json_encode($channelParts));
//        $baseChannel = $channelParts[0] ?? '';
//        $channelParam = $channelParts[1] ?? '';
//
//        Log::info('Validating private channel', [
//            'user_id' => $userId,
//            'base_channel' => $baseChannel,
//            'channel_param' => $channelParam
//        ]);
//
//        switch ($baseChannel) {
//            case 'user':
//                // User can only subscribe to their own user channel
//                return $userId === $channelParam;
//
//            case 'session':
//                // For session channels, validate email access
//                return $this->validateSessionChannel($userId, $channelParam);
//
//            default:
//                return false;
//        }
//    }

    protected function validateSessionChannel($userId, $email): bool
    {
        $decodedEmail = urldecode($email);

        Log::info('Validating session channel', [
            'user_id' => $userId,
            'email' => $decodedEmail
        ]);

        // Basic validation
        return !empty($decodedEmail);
    }

    protected function generateAuthResponse($socketId, $channelName): array
    {
        $pusherConfig = config('broadcasting.connections.pusher');
        Log::info("Pusher Config".json_encode($pusherConfig));
        if (!$pusherConfig) {
            throw new \Exception('Pusher configuration not found');
        }

        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $pusherConfig['secret']);

        return [
            'auth' => $pusherConfig['key'] . ':' . $signature
        ];
    }
}