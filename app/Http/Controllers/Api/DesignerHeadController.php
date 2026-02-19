<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerApplication;
use App\Models\DesignerProfile;
use App\Models\DesignerWallet;
use App\Models\DesignSubmission;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DesignerHeadController extends Controller
{
    /**
     * Get all pending applications
     */
    public function getApplications(Request $request)
    {
        $status = $request->input('status', 'pending');
        $perPage = $request->input('per_page', 20);

        $applications = DesignerApplication::with('reviewer')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $applications->map(function ($app) {
                return [
                    'id' => $app->id,
                    'name' => $app->name,
                    'email' => $app->email,
                    'phone' => $app->phone,
                    'city' => $app->city,
                    'state' => $app->state,
                    'experience' => $app->experience,
                    'experience_level' => $app->experience_level,
                    'skills' => $app->skills,
                    'portfolio_links' => $app->portfolio_links,
                    'uploaded_samples' => $app->uploaded_samples ? array_map(fn($path) => Storage::url($path), $app->uploaded_samples) : [],
                    'selected_types' => $app->selected_types,
                    'selected_categories' => $app->selected_categories,
                    'selected_goals' => $app->selected_goals,
                    'status' => $app->status,
                    'submitted_at' => $app->created_at,
                    'reviewed_by' => $app->reviewer ? $app->reviewer->name : null,
                    'reviewed_at' => $app->reviewed_at,
                    'rejection_reason' => $app->rejection_reason,
                ];
            }),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'total_pages' => $applications->lastPage(),
                'total' => $applications->total(),
                'per_page' => $applications->perPage(),
            ]
        ]);
    }

    /**
     * Approve application and create designer account
     */
    public function approveApplication(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $application = DesignerApplication::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Application already processed'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create user account
            $user = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'password' => Hash::make('Designer@123'), // Default password
                'user_type' => UserRole::DESIGNER_EMPLOYEE->id(),
                'status' => 1,
            ]);

            // Create designer profile
            $profile = DesignerProfile::create([
                'user_id' => $user->id,
                'application_id' => $application->id,
                'display_name' => $application->name,
                'commission_rate' => $request->commission_rate ?? 30.00,
                'is_active' => true,
            ]);

            // Create wallet
            DesignerWallet::create([
                'designer_id' => $profile->id,
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'pending_amount' => 0,
                'withdrawal_threshold' => 500.00,
            ]);

            // Update application
            $application->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully! Designer account created.',
                'data' => [
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                    'email' => $user->email,
                    'default_password' => 'Designer@123',
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject application
     */
    public function rejectApplication(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $application = DesignerApplication::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Application already processed'
            ], 400);
        }

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application rejected successfully'
        ]);
    }

    /**
     * Get design submissions for review
     */
    public function getDesignSubmissions(Request $request)
    {
        $status = $request->input('status', 'pending_designer_head');
        $perPage = $request->input('per_page', 20);

        $designs = DesignSubmission::with(['designer.user', 'seoDetails'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $designs->map(function ($design) {
                return [
                    'id' => $design->id,
                    'title' => $design->title,
                    'description' => $design->description,
                    'category' => $design->category,
                    'designer_name' => $design->designer->display_name,
                    'designer_email' => $design->designer->user->email,
                    'design_file' => Storage::url($design->design_file_path),
                    'preview_images' => array_map(fn($path) => Storage::url($path), $design->preview_images ?? []),
                    'tags' => $design->tags,
                    'status' => $design->status,
                    'submitted_at' => $design->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $designs->currentPage(),
                'total_pages' => $designs->lastPage(),
                'total' => $designs->total(),
                'per_page' => $designs->perPage(),
            ]
        ]);
    }

    /**
     * Approve design (moves to SEO head)
     */
    public function approveDesign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $design = DesignSubmission::find($id);

        if (!$design) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found'
            ], 404);
        }

        if ($design->status !== 'pending_designer_head') {
            return response()->json([
                'success' => false,
                'message' => 'Design already processed'
            ], 400);
        }

        $design->update([
            'status' => 'pending_seo',
            'designer_head_notes' => $request->notes,
            'designer_head_reviewed_by' => Auth::id(),
            'designer_head_reviewed_at' => now(),
        ]);

        // Update designer stats
        $design->designer->increment('approved_designs');

        return response()->json([
            'success' => true,
            'message' => 'Design approved! Sent to SEO head for final review.'
        ]);
    }

    /**
     * Reject design
     */
    public function rejectDesign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $design = DesignSubmission::find($id);

        if (!$design) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found'
            ], 404);
        }

        if ($design->status !== 'pending_designer_head') {
            return response()->json([
                'success' => false,
                'message' => 'Design already processed'
            ], 400);
        }

        $design->update([
            'status' => 'rejected_by_designer_head',
            'designer_head_notes' => $request->notes,
            'designer_head_reviewed_by' => Auth::id(),
            'designer_head_reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Design rejected'
        ]);
    }
}
