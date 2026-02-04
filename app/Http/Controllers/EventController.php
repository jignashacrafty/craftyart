<?php

namespace App\Http\Controllers;

use App\Events\PrivateUserEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function sendPrivateEvent(Request $request): JsonResponse
    {
        Log::channel('broadcasting')->info('📨 SEND_PRIVATE_EVENT_REQUEST', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        $request->validate([
            'message' => 'required|string',
            'user_id' => 'required|integer'
        ]);

        $userId = $request->user_id;
        $message = $request->message;
        $customData = $request->data ?? [];

        Log::channel('broadcasting')->info('🚀 CREATING_PRIVATE_EVENT', [
            'target_user_id' => $userId,
            'message' => $message,
            'custom_data' => $customData,
            'event_class' => PrivateUserEvent::class,
            'channel' => 'private-user.' . $userId
        ]);

        // Trigger the event
        event(new PrivateUserEvent($userId, $message, $customData));

        Log::channel('broadcasting')->info('✅ PRIVATE_EVENT_SENT_SUCCESS', [
            'target_user_id' => $userId,
            'message_length' => strlen($message),
            'sent_at' => now()->toISOString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Private event sent to user ' . $userId,
            'user_id' => $userId,
            'timestamp' => now()->toISOString()
        ]);
    }

    // New method to send to specific users
    public function sendToUser1(Request $request): JsonResponse
    {
        Log::channel('broadcasting')->info('👤 SEND_TO_USER1_REQUEST', [
            'ip' => $request->ip(),
            'data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        $request->validate([
            'message' => 'required|string'
        ]);

        $message = $request->message;
        $eventData = [
            'type' => 'direct',
            'target' => 'user-1',
            'sent_via' => 'direct_method',
            'sent_at' => now()->toISOString()
        ];

        Log::channel('broadcasting')->info('🎯 SENDING_TO_USER1', [
            'message' => $message,
            'event_data' => $eventData
        ]);

        event(new PrivateUserEvent(1, $message, $eventData));

        Log::channel('broadcasting')->info('✅ USER1_EVENT_SENT', [
            'message_length' => strlen($message),
            'sent_at' => now()->toISOString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Event sent to User 1',
            'target_user' => 1,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function sendToUser2(Request $request): JsonResponse
    {
        Log::channel('broadcasting')->info('👤 SEND_TO_USER2_REQUEST', [
            'ip' => $request->ip(),
            'data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        $request->validate([
            'message' => 'required|string'
        ]);

        $message = $request->message;
        $eventData = [
            'type' => 'direct',
            'target' => 'user-2',
            'sent_via' => 'direct_method',
            'sent_at' => now()->toISOString()
        ];

        Log::channel('broadcasting')->info('🎯 SENDING_TO_USER2', [
            'message' => $message,
            'event_data' => $eventData
        ]);

        event(new PrivateUserEvent(2, $message, $eventData));

        Log::channel('broadcasting')->info('✅ USER2_EVENT_SENT', [
            'message_length' => strlen($message),
            'sent_at' => now()->toISOString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Event sent to User 2',
            'target_user' => 2,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function sendToUser3(Request $request): JsonResponse
    {
        Log::channel('broadcasting')->info('👤 SEND_TO_USER3_REQUEST', [
            'ip' => $request->ip(),
            'data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        $request->validate([
            'message' => 'required|string'
        ]);

        $message = $request->message;
        $eventData = [
            'type' => 'direct',
            'target' => 'user-3',
            'sent_via' => 'direct_method',
            'sent_at' => now()->toISOString()
        ];

        Log::channel('broadcasting')->info('🎯 SENDING_TO_USER3', [
            'message' => $message,
            'event_data' => $eventData
        ]);

        event(new PrivateUserEvent(3, $message, $eventData));

        Log::channel('broadcasting')->info('✅ USER3_EVENT_SENT', [
            'message_length' => strlen($message),
            'sent_at' => now()->toISOString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Event sent to User 3',
            'target_user' => 3,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function sendToAllUsers(Request $request): JsonResponse
    {
        Log::channel('broadcasting')->info('📢 SEND_TO_ALL_USERS_REQUEST', [
            'ip' => $request->ip(),
            'data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        $request->validate([
            'message' => 'required|string'
        ]);

        $users = [1, 2, 3];
        $message = $request->message;
        $eventData = [
            'type' => 'broadcast',
            'target' => 'all-users',
            'sent_via' => 'broadcast_method',
            'total_recipients' => count($users),
            'sent_at' => now()->toISOString()
        ];

        Log::channel('broadcasting')->info('🌐 BROADCASTING_TO_ALL_USERS', [
            'message' => $message,
            'target_users' => $users,
            'event_data' => $eventData
        ]);

        $sentCount = 0;
        foreach ($users as $userId) {
            event(new PrivateUserEvent($userId, "Broadcast: " . $message, $eventData));
            $sentCount++;

            Log::channel('broadcasting')->info('📤 BROADCAST_EVENT_SENT', [
                'user_id' => $userId,
                'message' => "Broadcast: " . $message,
                'sequence' => $sentCount
            ]);
        }

        Log::channel('broadcasting')->info('✅ BROADCAST_COMPLETED', [
            'total_events_sent' => $sentCount,
            'target_users' => $users,
            'completed_at' => now()->toISOString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Event sent to all users (1, 2, 3)',
            'total_recipients' => $sentCount,
            'target_users' => $users,
            'timestamp' => now()->toISOString()
        ]);
    }

    // Add a test method to check if controller is working
    public function testConnection(): JsonResponse
    {
        Log::channel('broadcasting')->info('🔧 TEST_CONNECTION_CALLED', [
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'EventController is working!',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment()
        ]);
    }
}