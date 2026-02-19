<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function sendVerificationOTP(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Not implemented'
        ], 501);
    }

    public function verifyOTP(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Not implemented'
        ], 501);
    }
}
