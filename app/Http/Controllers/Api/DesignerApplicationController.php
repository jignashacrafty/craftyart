<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerApplication;
use App\Events\NewDesignerApplication;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DesignerApplicationController extends Controller
{
    /**
     * Submit designer application (Public API - No Auth)
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:designer_applications,email',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'experience' => 'nullable|string',
            'experience_level' => 'nullable|in:entry-level,mid-level,senior,expert',
            'skills' => 'nullable|string',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'design_samples' => 'nullable|array',
            'design_samples.*' => 'file|mimes:jpg,jpeg,png,pdf,ai,psd|max:10240', // 10MB max
            'selected_types' => 'nullable|array',
            'selected_types.*' => 'exists:designer_types,id',
            'selected_categories' => 'nullable|array',
            'selected_categories.*' => 'exists:designer_categories,id',
            'selected_goals' => 'nullable|array',
            'selected_goals.*' => 'exists:designer_goals,id',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(422, false, "Validation failed", [
                    'errors' => $validator->errors()->toArray()
                ])
            );
        }

        $data = $validator->validated();

        // Handle file uploads
        $uploadedSamples = [];
        if ($request->hasFile('design_samples')) {
            foreach ($request->file('design_samples') as $file) {
                $path = $file->store('designer_applications/samples', 'public');
                $uploadedSamples[] = $path;
            }
        }

        $data['uploaded_samples'] = $uploadedSamples;
        $data['status'] = 'pending';

        $application = DesignerApplication::create($data);

        // Broadcast event for real-time updates using direct HTTP API
        \App\Http\Controllers\WebSocketBroadcastController::broadcastDesignerApplicationCreated($application);

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, "Application submitted successfully! We will review and get back to you soon.", [
                'application_id' => $application->id,
                'status' => $application->status,
            ])
        );
    }

    /**
     * Check application status (Public API)
     * Also allows approve/reject with proper authentication
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'action' => 'nullable|in:approve,reject', // Optional action
            'rejection_reason' => 'required_if:action,reject|string|min:10',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(422, false, "Validation failed", [
                    'errors' => $validator->errors()->toArray()
                ])
            );
        }

        $application = DesignerApplication::where('email', $request->email)
            ->with('reviewer')
            ->first();

        if (!$application) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, "No application found with this email", [])
            );
        }

        // If action is provided, handle approve/reject
        if ($request->filled('action')) {
            // Check authentication for approve/reject actions
            $token = $request->bearerToken();

            if (!$token) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Authentication required for approve/reject actions")
                );
            }

            try {
                $decoded = \App\Helpers\JwtHelper::decode($token);
                $userId = $decoded->id ?? $decoded->user_id ?? null;

                if (!$userId) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(401, false, "Invalid authentication token")
                    );
                }

                // Check if user is admin/designer head
                $user = \App\Models\User::find($userId);
                if (!$user || !in_array($user->user_type, [1, 2])) { // 1=admin, 2=designer_head
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(403, false, "Only admin or designer head can approve/reject applications")
                    );
                }

                // Perform action
                if ($request->action === 'approve') {
                    if ($application->status === 'approved') {
                        return ResponseHandler::sendResponse(
                            $request,
                            new ResponseInterface(400, false, "Application is already approved")
                        );
                    }

                    $application->update([
                        'status' => 'approved',
                        'reviewed_by' => $userId,
                        'reviewed_at' => now(),
                        'rejection_reason' => null,
                    ]);

                    // Create user account and designer profile
                    $newUser = \App\Models\User::firstOrCreate(
                        ['email' => $application->email],
                        [
                            'name' => $application->name,
                            'password' => bcrypt('temporary_password_' . rand(100000, 999999)),
                            'user_type' => 3, // Designer
                            'status' => 1,
                        ]
                    );

                    // Create designer profile
                    $profile = \App\Models\DesignerProfile::firstOrCreate(
                        ['user_id' => $newUser->id],
                        [
                            'application_id' => $application->id,
                            'display_name' => $application->name,
                            'bio' => $application->experience ?? 'Professional designer',
                            'specializations' => $application->selected_categories ?? [],
                            'commission_rate' => 30,
                            'is_active' => true,
                        ]
                    );

                    // Create wallet
                    \App\Models\DesignerWallet::firstOrCreate(
                        ['designer_id' => $profile->id],
                        [
                            'balance' => 0,
                            'total_earned' => 0,
                            'total_withdrawn' => 0,
                            'withdrawal_threshold' => 500,
                        ]
                    );

                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(200, true, "Application approved successfully! Designer profile created.", [
                            'application_id' => $application->id,
                            'status' => 'approved',
                            'designer_profile_id' => $profile->id,
                            'user_id' => $newUser->id,
                        ])
                    );
                }

                if ($request->action === 'reject') {
                    if ($application->status === 'rejected') {
                        return ResponseHandler::sendResponse(
                            $request,
                            new ResponseInterface(400, false, "Application is already rejected")
                        );
                    }

                    $application->update([
                        'status' => 'rejected',
                        'reviewed_by' => $userId,
                        'reviewed_at' => now(),
                        'rejection_reason' => $request->rejection_reason,
                    ]);

                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(200, true, "Application rejected successfully", [
                            'application_id' => $application->id,
                            'status' => 'rejected',
                            'rejection_reason' => $application->rejection_reason,
                        ])
                    );
                }

            } catch (\Exception $e) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Invalid or expired token: " . $e->getMessage())
                );
            }
        }

        // Just check status (no action)
        $responseData = [
            'application_id' => $application->id,
            'name' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'city' => $application->city,
            'state' => $application->state,
            'country' => $application->country,
            'experience' => $application->experience,
            'experience_level' => $application->experience_level,
            'skills' => $application->skills,
            'portfolio_links' => $application->portfolio_links,
            'selected_types' => $application->selected_types,
            'selected_categories' => $application->selected_categories,
            'selected_goals' => $application->selected_goals,
            'status' => $application->status,
            'submitted_at' => $application->created_at->toISOString(),
            'reviewed_at' => $application->reviewed_at?->toISOString(),
            'reviewed_by' => $application->reviewer?->name,
            'rejection_reason' => $application->rejection_reason,
            'can_reapply' => $application->status === 'rejected',
        ];

        $message = match ($application->status) {
            'pending' => 'Your application is under review. We will get back to you soon.',
            'approved' => 'Congratulations! Your application has been approved.',
            'rejected' => 'Your application was not approved. You can submit a new application.',
            default => 'Application found'
        };

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, $message, $responseData)
        );
    }
}
