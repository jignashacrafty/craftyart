<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JwtHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderUserAuthController extends Controller
{
    /**
     * POST /api/order-user-auth/login
     * Simple login endpoint for Order User APIs
     * Returns encrypted JSON response with token
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
                'device_id' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(422, false, "Validation failed", [
                        'errors' => $validator->errors()->toArray()
                    ])
                );
            }

            $email = $request->input('email');
            $password = $request->input('password');
            $deviceId = $request->input('device_id', 'web-' . time());

            // Find user in users table
            $user = User::where('email', $email)->first();

            if (!$user) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Email is not registered", [])
                );
            }

            // Verify password
            if (!Hash::check($password, $user->password)) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Incorrect password", [])
                );
            }

            // Check if user is active
            if ($user->status == 0) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(403, false, "User account is inactive", [])
                );
            }

            // Generate JWT token
            $jwtPayload = [
                'id' => $user->id,
                'email' => $user->email,
                'device_id' => $deviceId,
                'name' => $user->name,
            ];

            $token = JwtHelper::generate($jwtPayload, 30); // 30 days expiry

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, "Login successful", [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'user_type' => $user->user_type,
                        'status' => $user->status,
                    ]
                ])
            );

        } catch (\Exception $e) {
            Log::error('Order User Login Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, "Login error: " . $e->getMessage(), [])
            );
        }
    }

    /**
     * POST /api/order-user-auth/verify-token
     * Verify if token is valid
     */
    public function verifyToken(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            
            if (!$token) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Token not provided", [])
                );
            }

            $token = str_replace('Bearer ', '', $token);

            try {
                $decoded = JwtHelper::decode($token);
                
                $user = User::where('id', $decoded->id)->first();
                
                if (!$user) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(401, false, "User not found", [])
                    );
                }

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, "Token is valid", [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ])
                );

            } catch (\Exception $e) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Invalid or expired token", [])
                );
            }

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, "Token verification error", [])
            );
        }
    }

    /**
     * POST /api/order-user-auth/logout
     * Logout user (optional - just remove token from client side)
     */
    public function logout(Request $request)
    {
        try {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, "Logged out successfully. Please remove token from client.", [])
            );

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, "Logged out successfully", [])
            );
        }
    }
}
