<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerProfile;
use App\Models\DesignSubmission;
use App\Models\DesignSeoDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignerController extends Controller
{
    /**
     * Get designer profile
     */
    public function getProfile(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::with(['wallet', 'user'])->where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Designer profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $profile->id,
                'display_name' => $profile->display_name,
                'bio' => $profile->bio,
                'profile_image' => $profile->profile_image ? Storage::url($profile->profile_image) : null,
                'specializations' => $profile->specializations,
                'commission_rate' => $profile->commission_rate,
                'is_active' => $profile->is_active,
                'stats' => [
                    'total_designs' => $profile->total_designs,
                    'approved_designs' => $profile->approved_designs,
                    'live_designs' => $profile->live_designs,
                    'total_earnings' => $profile->total_earnings,
                ],
                'wallet' => [
                    'balance' => $profile->wallet->balance ?? 0,
                    'total_earned' => $profile->wallet->total_earned ?? 0,
                    'total_withdrawn' => $profile->wallet->total_withdrawn ?? 0,
                    'can_withdraw' => $profile->wallet ? $profile->wallet->canWithdraw() : false,
                    'withdrawal_threshold' => $profile->wallet->withdrawal_threshold ?? 500,
                ],
            ]
        ]);
    }

    /**
     * Submit new design
     */
    public function submitDesign(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Designer profile not found'
            ], 404);
        }

        if (!$profile->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your designer account is inactive'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:template,video,sticker,frame,gif,vector,bg',
            'category_id' => 'nullable|integer',
            'design_file' => 'required|file|mimes:jpg,jpeg,png,pdf,ai,psd,svg,mp4|max:51200', // 50MB
            'preview_images' => 'nullable|array',
            'preview_images.*' => 'file|mimes:jpg,jpeg,png|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            // SEO Details
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Upload design file
        $designPath = $request->file('design_file')->store('designs/' . $profile->id, 'public');

        // Upload preview images
        $previewPaths = [];
        if ($request->hasFile('preview_images')) {
            foreach ($request->file('preview_images') as $preview) {
                $previewPaths[] = $preview->store('designs/' . $profile->id . '/previews', 'public');
            }
        }

        // Create design submission
        $design = DesignSubmission::create([
            'designer_id' => $profile->id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'category_id' => $request->category_id,
            'design_file_path' => $designPath,
            'preview_images' => $previewPaths,
            'tags' => $request->tags,
            'status' => 'pending_designer_head',
        ]);

        // Create SEO details if provided
        if ($request->filled('meta_title') || $request->filled('meta_description')) {
            DesignSeoDetail::create([
                'design_submission_id' => $design->id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'slug' => Str::slug($request->meta_title ?? $request->title),
                'keywords' => $request->keywords,
            ]);
        }

        // Update designer stats
        $profile->increment('total_designs');

        return response()->json([
            'success' => true,
            'message' => 'Design submitted successfully! It will be reviewed by the designer head.',
            'data' => [
                'design_id' => $design->id,
                'status' => $design->status,
            ]
        ], 201);
    }

    /**
     * Get designer's designs
     */
    public function getDesigns(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Designer profile not found'
            ], 404);
        }

        $status = $request->input('status');
        $perPage = $request->input('per_page', 20);

        $query = DesignSubmission::where('designer_id', $profile->id)
            ->with(['seoDetails']);

        if ($status) {
            $query->where('status', $status);
        }

        $designs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $designs->map(function ($design) {
                return [
                    'id' => $design->id,
                    'title' => $design->title,
                    'category' => $design->category,
                    'status' => $design->status,
                    'preview_image' => $design->preview_images && count($design->preview_images) > 0 
                        ? Storage::url($design->preview_images[0]) 
                        : null,
                    'total_sales' => $design->total_sales,
                    'total_revenue' => $design->total_revenue,
                    'submitted_at' => $design->created_at,
                    'published_at' => $design->published_at,
                    'designer_head_notes' => $design->designer_head_notes,
                    'seo_head_notes' => $design->seo_head_notes,
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
     * Get single design details
     */
    public function getDesignDetails($id)
    {
        $user = Auth::user();
        $profile = DesignerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Designer profile not found'
            ], 404);
        }

        $design = DesignSubmission::with(['seoDetails', 'sales'])
            ->where('id', $id)
            ->where('designer_id', $profile->id)
            ->first();

        if (!$design) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $design->id,
                'title' => $design->title,
                'description' => $design->description,
                'category' => $design->category,
                'status' => $design->status,
                'design_file' => Storage::url($design->design_file_path),
                'preview_images' => array_map(fn($path) => Storage::url($path), $design->preview_images ?? []),
                'tags' => $design->tags,
                'total_sales' => $design->total_sales,
                'total_revenue' => $design->total_revenue,
                'submitted_at' => $design->created_at,
                'published_at' => $design->published_at,
                'designer_head_notes' => $design->designer_head_notes,
                'seo_head_notes' => $design->seo_head_notes,
                'seo_details' => $design->seoDetails ? [
                    'meta_title' => $design->seoDetails->meta_title,
                    'meta_description' => $design->seoDetails->meta_description,
                    'slug' => $design->seoDetails->slug,
                    'keywords' => $design->seoDetails->keywords,
                    'is_featured' => $design->seoDetails->is_featured,
                    'is_trending' => $design->seoDetails->is_trending,
                ] : null,
            ]
        ]);
    }
}
