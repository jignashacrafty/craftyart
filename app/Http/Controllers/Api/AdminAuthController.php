<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JwtHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
  /**
   * Simple login for admin/designer head users (no device tracking)
   * Use this for Postman/API testing
   * 
   * Response is encrypted by default
   * Add ?showDecoded=1 to URL to get unencrypted response for testing
   * 
   * Example: POST /api/admin/login?showDecoded=1
   */
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return ResponseHandler::sendResponse(
        $request,
        new ResponseInterface(422, false, "Validation failed", [
          'errors' => $validator->errors()->toArray()
        ])
      );
    }

    // Find user in users table (admin/staff users)
    $user = User::where('email', $request->email)->first();

    if (!$user) {
      return ResponseHandler::sendResponse(
        $request,
        new ResponseInterface(401, false, "Email is not registered")
      );
    }

    // Check password
    if (!Hash::check($request->password, $user->password)) {
      return ResponseHandler::sendResponse(
        $request,
        new ResponseInterface(401, false, "Incorrect password")
      );
    }

    // Check if user is active
    if ($user->status == 0) {
      return ResponseHandler::sendResponse(
        $request,
        new ResponseInterface(403, false, "User account is inactive")
      );
    }

    // Generate JWT token
    $jwtPayload = [
      'id' => $user->id,
      'email' => $user->email,
      'user_type' => $user->user_type,
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
  }

  /**
   * Get current authenticated user info
   * 
   * Response is encrypted by default
   * Add ?showDecoded=1 to URL to get unencrypted response
   */
  public function me(Request $request)
  {
    $user = $request->user();

    if (!$user) {
      return ResponseHandler::sendResponse(
        $request,
        new ResponseInterface(401, false, "User not authenticated")
      );
    }

    return ResponseHandler::sendResponse(
      $request,
      new ResponseInterface(200, true, "User data retrieved", [
        'user' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'user_type' => $user->user_type,
          'status' => $user->status,
        ]
      ])
    );
  }

  /**
   * Logout (optional - JWT tokens are stateless)
   * 
   * Response is encrypted by default
   * Add ?showDecoded=1 to URL to get unencrypted response
   */
  public function logout(Request $request)
  {
    return ResponseHandler::sendResponse(
      $request,
      new ResponseInterface(200, true, "Logged out successfully. Please remove the token from your client.")
    );
  }
}
