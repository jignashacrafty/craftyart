<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerApplication;
use App\Models\DesignerProfile;
use App\Models\DesignerWallet;
use App\Models\DesignerType;
use App\Models\DesignerCategory;
use App\Models\DesignerGoal;
use App\Models\WalletSetting;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DesignerEnrollmentController extends Controller
{
    /**
     * Check if user is already enrolled
     */
    public function checkEnrollment(Request $request)
    {
        $user = Auth::user();
        
        $application = DesignerApplication::where('user_id', $user->id)
            ->where('is_enrolled', true)
            ->first();

        if ($application) {
            $profile = DesignerProfile::where('user_id', $user->id)->first();
            
            return response()->json([
                'success' => true,
                'is_enrolled' => true,
                'has_chosen_plan' => $application->has_chosen_plan,
                'profile' => $profile ? [
                    'id' => $profile->id,
                    'display_name' => $profile->display_name,
                    'is_active' => $profile->is_active,
                ] : null,
            ]);
        }

        return response()->json([
            'success' => true,
            'is_enrolled' => false,
            'has_chosen_plan' => false,
        ]);
    }

    /**
     * Get enrollment options (types, categories, goals) - All in one API
     */
    public function getEnrollmentOptions(Request $request)
    {
        return ResponseHandler::sendResponse($request,new ResponseInterface(200,true,"Data Fetched",[
                'types' => DesignerType::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'])
                    ->toArray(),
                'categories' => DesignerCategory::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'])
                    ->toArray(),
                'goals' => DesignerGoal::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->get(['id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'])
                    ->toArray(),
            ]));
    }

    /**
     * Submit enrollment application
     */
    public function submitEnrollment(Request $request)
    {
        $user = Auth::user();

        // Check if already enrolled
        $existingApplication = DesignerApplication::where('user_id', $user->id)
            ->where('is_enrolled', true)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in the designer program'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'experience' => 'required|string',
            'experience_level' => 'required|in:entry-level,mid-level,senior,expert',
            'skills' => 'required|string',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'uploaded_samples' => 'nullable|array',
            'selected_types' => 'required|array|min:1',
            'selected_types.*' => 'exists:designer_types,id',
            'selected_categories' => 'required|array|min:1',
            'selected_categories.*' => 'exists:designer_categories,id',
            'selected_goals' => 'required|array|min:1',
            'selected_goals.*' => 'exists:designer_goals,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create application
            $application = DesignerApplication::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'experience' => $request->experience,
                'experience_level' => $request->experience_level,
                'skills' => $request->skills,
                'portfolio_links' => $request->portfolio_links,
                'uploaded_samples' => $request->uploaded_samples,
                'selected_types' => $request->selected_types,
                'selected_categories' => $request->selected_categories,
                'selected_goals' => $request->selected_goals,
                'status' => 'pending',
                'is_enrolled' => true,
                'enrolled_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment application submitted successfully! Your application is under review.',
                'data' => [
                    'application_id' => $application->id,
                    'status' => $application->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit enrollment application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Choose plan after enrollment
     */
    public function choosePlan(Request $request)
    {
        $user = Auth::user();

        $application = DesignerApplication::where('user_id', $user->id)
            ->where('is_enrolled', true)
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'You must enroll first before choosing a plan'
            ], 400);
        }

        if ($application->has_chosen_plan) {
            return response()->json([
                'success' => false,
                'message' => 'You have already chosen a plan'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'plan' => 'required|string|in:free,basic,premium,enterprise',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $application->has_chosen_plan = true;
        $application->chosen_plan = $request->plan;
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Plan selected successfully!',
            'data' => [
                'chosen_plan' => $application->chosen_plan,
            ]
        ]);
    }

    /**
     * Get enrollment status
     */
    public function getEnrollmentStatus(Request $request)
    {
        $user = Auth::user();

        $application = DesignerApplication::where('user_id', $user->id)->first();

        if (!$application) {
            return response()->json([
                'success' => true,
                'enrolled' => false,
                'message' => 'No application found'
            ]);
        }

        $profile = DesignerProfile::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'enrolled' => $application->is_enrolled,
            'data' => [
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'is_enrolled' => $application->is_enrolled,
                    'enrolled_at' => $application->enrolled_at,
                    'has_chosen_plan' => $application->has_chosen_plan,
                    'chosen_plan' => $application->chosen_plan,
                    'selected_types' => $application->selected_types,
                    'selected_categories' => $application->selected_categories,
                    'selected_goals' => $application->selected_goals,
                ],
                'profile' => $profile ? [
                    'id' => $profile->id,
                    'display_name' => $profile->display_name,
                    'bio' => $profile->bio,
                    'is_active' => $profile->is_active,
                    'total_designs' => $profile->total_designs,
                    'approved_designs' => $profile->approved_designs,
                ] : null,
            ]
        ]);
    }

    /**
     * Get all active designer types for enrollment form
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTypes()
    {
        $types = DesignerType::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'])
            ->toArray();

        return ResponseHandler::sendResponse(
            request(),
            new ResponseInterface(200, true, "Data Fetched", ['data' => $types])
        );
    }

    /**
     * Get all active designer categories for enrollment form
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = DesignerCategory::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'])
            ->toArray();

        return ResponseHandler::sendResponse(
            request(),
            new ResponseInterface(200, true, "Data Fetched", ['data' => $categories])
        );
    }

    /**
     * Get all active designer goals for enrollment form
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGoals()
    {
        $goals = DesignerGoal::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'])
            ->toArray();

        return ResponseHandler::sendResponse(
            request(),
            new ResponseInterface(200, true, "Data Fetched", ['data' => $goals])
        );
    }
}
