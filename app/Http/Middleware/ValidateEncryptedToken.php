<?php

namespace App\Http\Middleware;

use App\Helpers\JwtHelper;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateEncryptedToken
{
    /**
     * Handle an incoming request.
     * Validates the encrypted JWT token from your existing auth system
     */
    public function handle(Request $request, Closure $next)
    {
        // Get token from Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization header missing'
            ], 401);
        }

        // Extract token (supports both "Bearer TOKEN" and just "TOKEN")
        $token = str_replace('Bearer ', '', $authHeader);
        
        try {
            // Decode the JWT token using your existing JwtHelper
            $decoded = JwtHelper::decode($token);
            
            // Get user from decoded token
            $userId = $decoded->id ?? null;
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token: user ID not found'
                ], 401);
            }
            
            // Load user from users table
            $user = User::where('id', $userId)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }
            
            // Check if user is active
            if ($user->status == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive'
                ], 403);
            }
            
            // Attach user to request for use in controller
            $request->merge(['authenticated_user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            return $next($request);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired. Please login again.'
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token signature'
            ], 401);
        } catch (\Exception $e) {
            Log::error('Token validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }
}
