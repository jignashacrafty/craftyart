<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignSubmission;
use App\Models\DesignSeoDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoHeadController extends Controller
{
    /**
     * Get designs pending SEO approval
     */
    public function getDesignSubmissions(Request $request)
    {
        $status = $request->input('status', 'pending_seo');
        $perPage = $request->input('per_page', 20);

        $designs = DesignSubmission::with(['designer.user', 'seoDetails', 'designerHeadReviewer'])
            ->whereIn('status', ['pending_seo', 'approved_by_seo', 'rejected_by_seo', 'live'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
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
                    'design_file' => Storage::url($design->design_file_path),
                    'preview_images' => array_map(fn($path) => Storage::url($path), $design->preview_images ?? []),
                    'tags' => $design->tags,
                    'status' => $design->status,
                    'designer_head_notes' => $design->designer_head_notes,
                    'designer_head_reviewed_by' => $design->designerHeadReviewer ? $design->designerHeadReviewer->name : null,
                    'submitted_at' => $design->created_at,
                    'seo_details' => $design->seoDetails ? [
                        'meta_title' => $design->seoDetails->meta_title,
                        'meta_description' => $design->seoDetails->meta_description,
                        'slug' => $design->seoDetails->slug,
                        'keywords' => $design->seoDetails->keywords,
                        'is_featured' => $design->seoDetails->is_featured,
                        'is_trending' => $design->seoDetails->is_trending,
                    ] : null,
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
     * Update SEO details and approve design (goes live)
     */
    public function approveDesign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'meta_title' => 'required|string|max:255',
            'meta_description' => 'required|string|max:500',
            'slug' => 'nullable|string|max:255|unique:design_seo_details,slug',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string',
            'og_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'is_featured' => 'nullable|boolean',
            'is_trending' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $design = DesignSubmission::with('seoDetails')->find($id);

        if (!$design) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found'
            ], 404);
        }

        if ($design->status !== 'pending_seo') {
            return response()->json([
                'success' => false,
                'message' => 'Design is not pending SEO approval'
            ], 400);
        }

        // Handle OG image upload
        $ogImagePath = null;
        if ($request->hasFile('og_image')) {
            $ogImagePath = $request->file('og_image')->store('seo/og-images', 'public');
        }

        // Generate slug if not provided
        $slug = $request->slug ?? Str::slug($request->meta_title);

        // Update or create SEO details
        if ($design->seoDetails) {
            $design->seoDetails->update([
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'slug' => $slug,
                'keywords' => $request->keywords,
                'og_image' => $ogImagePath ?? $design->seoDetails->og_image,
                'is_featured' => $request->is_featured ?? false,
                'is_trending' => $request->is_trending ?? false,
                'priority' => $request->priority ?? 0,
            ]);
        } else {
            DesignSeoDetail::create([
                'design_submission_id' => $design->id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'slug' => $slug,
                'keywords' => $request->keywords,
                'og_image' => $ogImagePath,
                'is_featured' => $request->is_featured ?? false,
                'is_trending' => $request->is_trending ?? false,
                'priority' => $request->priority ?? 0,
            ]);
        }

        // Update design status to live
        $design->update([
            'status' => 'live',
            'seo_head_notes' => $request->notes,
            'seo_head_reviewed_by' => Auth::id(),
            'seo_head_reviewed_at' => now(),
            'published_at' => now(),
        ]);

        // Update designer stats
        $design->designer->increment('live_designs');

        return response()->json([
            'success' => true,
            'message' => 'Design approved and published successfully!'
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

        if ($design->status !== 'pending_seo') {
            return response()->json([
                'success' => false,
                'message' => 'Design is not pending SEO approval'
            ], 400);
        }

        $design->update([
            'status' => 'rejected_by_seo',
            'seo_head_notes' => $request->notes,
            'seo_head_reviewed_by' => Auth::id(),
            'seo_head_reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Design rejected'
        ]);
    }

    /**
     * Update SEO details for live design
     */
    public function updateSeoDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'keywords' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'is_trending' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $design = DesignSubmission::with('seoDetails')->find($id);

        if (!$design || !$design->seoDetails) {
            return response()->json([
                'success' => false,
                'message' => 'Design or SEO details not found'
            ], 404);
        }

        $design->seoDetails->update($request->only([
            'meta_title',
            'meta_description',
            'keywords',
            'is_featured',
            'is_trending',
            'priority',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'SEO details updated successfully'
        ]);
    }
}
