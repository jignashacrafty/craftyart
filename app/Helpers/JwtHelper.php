<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class JwtHelper
{
    private static function secret()
    {
        return env('JWT_SECRET');
    }

    public static function generate(array $payload, int $days = 30): string
    {
        $now = Carbon::now()->timestamp;

        $token = array_merge($payload, [
            'iat' => $now,
            'exp' => Carbon::now()->addDays($days)->timestamp,
        ]);

        return JWT::encode($token, self::secret(), 'HS256');
    }

    public static function decode(string $token)
    {
        return JWT::decode($token, new Key(self::secret(), 'HS256'));
    }
}
