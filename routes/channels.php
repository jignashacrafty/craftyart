<?php

use App\Models\UserSession;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('user-{userId}', function ($user, $userId) {
    return $user && (string) $user->uid === (string) $userId;
});
Broadcast::channel('session-{email}', function ($user, $email) {
    return $user && $user->email === $email;
});



Broadcast::channel('private-user.{userId}', function ($user, $userId) {
    Log::channel('broadcasting')->info('🔐 CHANNEL_AUTHORIZATION', [
        'requested_user_id' => $userId,
        'authenticated_user' => $user ? $user->id : 'null',
        'timestamp' => now()->toISOString()
    ]);

    // For demo purposes, allow any user to access any private channel
    // In production, you would verify the authenticated user matches the channel
    return [
        'id' => (int) $userId,
        'name' => 'User ' . $userId,
        'authorized' => true,
        'timestamp' => now()->toISOString()
    ];
});