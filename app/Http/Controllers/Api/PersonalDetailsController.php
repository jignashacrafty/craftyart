<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalDetails;
use App\Models\UserData;
use App\Models\BrandKit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PersonalDetailsController extends Controller
{
    /**
     * Get personal details for a user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPersonalDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uid' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uid = $request->uid;
            
            Log::info('Fetching personal details for UID: ' . $uid);
            
            $user = UserData::where('uid', $uid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $personalDetails = PersonalDetails::where('uid', $uid)->first();
            
            // Get brand kit
            $brandKit = BrandKit::where('user_id', $uid)->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Personal details fetched successfully',
                'data' => [
                    'user' => [
                        'uid' => $user->uid,
                        'name' => $user->name,
                        'email' => $user->email,
                        'contact_no' => $user->number,
                    ],
                    'personal_details' => $personalDetails ? [
                        'user_name' => $personalDetails->user_name,
                        'bio' => $personalDetails->bio,
                        'country' => $personalDetails->country,
                        'state' => $personalDetails->state,
                        'city' => $personalDetails->city,
                        'address' => $personalDetails->address,
                        'interest' => $personalDetails->interest,
                        'purpose' => $personalDetails->purpose,
                        'usage' => $personalDetails->usage,
                        'reference' => $personalDetails->reference,
                        'language' => $personalDetails->language,
                    ] : null,
                    'brand_kit' => $brandKit ? [
                        'website' => $brandKit->website,
                        'role' => $brandKit->role,
                        'usage' => $brandKit->usage,
                    ] : null,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get Personal Details Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching personal details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update personal details for a user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePersonalDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uid' => 'required|string',
                'user_name' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'country' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'address' => 'nullable|string',
                'interest' => 'nullable|string',
                'purpose' => 'nullable|string',
                'usage' => 'nullable|string',
                'reference' => 'nullable|string',
                'language' => 'nullable|string|max:50',
                'website' => 'nullable|string|max:255',
                'role' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uid = $request->uid;
            
            Log::info('Updating personal details for UID: ' . $uid, [
                'request_data' => $request->except(['uid'])
            ]);
            
            $user = UserData::where('uid', $uid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Update or create personal details
            $personalDetails = PersonalDetails::updateOrCreate(
                ['uid' => $uid],
                [
                    'user_name' => $request->user_name ?? $user->name,
                    'bio' => $request->bio,
                    'country' => $request->country,
                    'state' => $request->state,
                    'city' => $request->city,
                    'address' => $request->address,
                    'interest' => $request->interest,
                    'purpose' => $request->purpose,
                    'usage' => $request->usage,
                    'reference' => $request->reference,
                    'language' => $request->language,
                ]
            );
            
            Log::info('Personal details saved successfully', [
                'uid' => $uid,
                'personal_details_id' => $personalDetails->id
            ]);
            
            // Update or create brand kit if website, role, or usage provided
            $brandKit = null;
            if ($request->has('website') || $request->has('role') || $request->has('usage')) {
                $brandKit = BrandKit::updateOrCreate(
                    ['user_id' => $uid],
                    array_filter([
                        'website' => $request->website,
                        'role' => $request->role,
                        'usage' => $request->usage,
                    ])
                );
                
                Log::info('Brand kit updated successfully', [
                    'uid' => $uid,
                    'brand_kit_id' => $brandKit->id
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Personal details updated successfully',
                'data' => [
                    'personal_details' => [
                        'id' => $personalDetails->id,
                        'uid' => $personalDetails->uid,
                        'user_name' => $personalDetails->user_name,
                        'bio' => $personalDetails->bio,
                        'country' => $personalDetails->country,
                        'state' => $personalDetails->state,
                        'city' => $personalDetails->city,
                        'address' => $personalDetails->address,
                        'interest' => $personalDetails->interest,
                        'purpose' => $personalDetails->purpose,
                        'usage' => $personalDetails->usage,
                        'reference' => $personalDetails->reference,
                        'language' => $personalDetails->language,
                        'updated_at' => $personalDetails->updated_at->toDateTimeString(),
                    ],
                    'brand_kit' => $brandKit ? [
                        'id' => $brandKit->id,
                        'website' => $brandKit->website,
                        'role' => $brandKit->role,
                        'usage' => $brandKit->usage,
                        'updated_at' => $brandKit->updated_at->toDateTimeString(),
                    ] : null,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Update Personal Details Error', [
                'uid' => $request->uid ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating personal details: ' . $e->getMessage()
            ], 500);
        }
    }
}
