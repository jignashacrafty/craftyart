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
     * Helper method to get designer profile from JWT token
     * Supports both admin tokens and app user tokens
     */
    private function getDesignerProfileFromToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return [
                'error' => ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Authentication token required")
                )
            ];
        }

        try {
            $decoded = \App\Helpers\JwtHelper::decode($token);
            $uid = $decoded->uid ?? null;
            $email = $decoded->email ?? null;
            $userId = $decoded->id ?? $decoded->user_id ?? null;

            $profile = null;
            $isAdminToken = $userId && !$uid;

            if ($isAdminToken) {
                // Admin token
                $profile = DesignerProfile::where('user_id', $userId)->first();
            } else {
                // App user token
                $userData = null;

                if ($uid) {
                    $userData = \App\Models\UserData::where('uid', $uid)->first();
                }

                if (!$userData && $email) {
                    $userData = \App\Models\UserData::where('email', $email)->first();
                }

                if (!$userData) {
                    return [
                        'error' => ResponseHandler::sendResponse(
                            $request,
                            new ResponseInterface(404, false, "User not found")
                        )
                    ];
                }

                if ($userData->creator != 1) {
                    return [
                        'error' => ResponseHandler::sendResponse(
                            $request,
                            new ResponseInterface(403, false, "Only creators can access designer features")
                        )
                    ];
                }

                $user = \App\Models\User::where('email', $userData->email)->first();
                if ($user) {
                    $profile = DesignerProfile::where('user_id', $user->id)->first();
                }
            }

            if (!$profile) {
                return [
                    'error' => ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(404, false, "Designer profile not found")
                    )
                ];
            }

            return ['profile' => $profile];

        } catch (\Exception $e) {
            return [
                'error' => ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Invalid or expired token: " . $e->getMessage())
                )
            ];
        }
    }

    /**
     * Get designer profile
     * Supports both:
     * 1. Approved designers (users table with designer_profiles)
     * 2. Creators (user_data table with creator = 1)
     */
    /**
     * Get designer profile
     * Supports both:
     * 1. Admin users with designer_profiles (token has 'id')
     * 2. App users who are creators (token has 'uid', creator=1 in user_data)
     */
    public function getProfile(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(401, false, "Authentication token required")
            );
        }

        try {
            $decoded = \App\Helpers\JwtHelper::decode($token);
            $uid = $decoded->uid ?? null;
            $email = $decoded->email ?? null;
            $userId = $decoded->id ?? $decoded->user_id ?? null;

            if (!$uid && !$email && !$userId) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(401, false, "Invalid token: missing user identifier")
                );
            }

            // Determine token type
            $isAdminToken = $userId && !$uid;

            if ($isAdminToken) {
                // Admin/Staff token - check designer_profiles table
                $profile = DesignerProfile::with(['wallet', 'user'])
                    ->where('user_id', $userId)
                    ->first();

                if ($profile) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(200, true, "Designer profile retrieved", [
                            'profile' => [
                                'id' => $profile->id,
                                'display_name' => $profile->display_name,
                                'bio' => $profile->bio,
                                'profile_image' => $profile->profile_image ? Storage::url($profile->profile_image) : null,
                                'specializations' => $profile->specializations,
                                'commission_rate' => $profile->commission_rate,
                                'is_active' => $profile->is_active,
                                'type' => 'approved_designer',
                            ],
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
                        ])
                    );
                }

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, "Designer profile not found. Admin users need to be assigned a designer profile first.")
                );
            }

            // App user token - look in user_data table
            $userData = null;

            if ($uid) {
                $userData = \App\Models\UserData::where('uid', $uid)->first();
            }

            if (!$userData && $email) {
                $userData = \App\Models\UserData::where('email', $email)->first();
            }

            if (!$userData) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, "User not found. Please login through the mobile app to access designer features.")
                );
            }

            // Check if user is a creator
            if ($userData->creator == 1) {
                // Check if they also have an approved designer profile
                $user = \App\Models\User::where('email', $userData->email)->first();
                $profile = null;

                if ($user) {
                    $profile = DesignerProfile::with(['wallet', 'user'])
                        ->where('user_id', $user->id)
                        ->first();
                }

                if ($profile) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(200, true, "Designer profile retrieved", [
                            'profile' => [
                                'id' => $profile->id,
                                'display_name' => $profile->display_name,
                                'bio' => $profile->bio,
                                'profile_image' => $profile->profile_image ? Storage::url($profile->profile_image) : null,
                                'specializations' => $profile->specializations,
                                'commission_rate' => $profile->commission_rate,
                                'is_active' => $profile->is_active,
                                'type' => 'approved_designer',
                            ],
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
                        ])
                    );
                }

                // Return creator profile
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, "Creator profile retrieved", [
                        'profile' => [
                            'uid' => $userData->uid,
                            'name' => $userData->name,
                            'email' => $userData->email,
                            'photo_uri' => $userData->photo_uri,
                            'is_creator' => true,
                            'creator' => $userData->creator,
                            'type' => 'creator',
                        ],
                        'message' => 'You are registered as a creator. To become an approved designer, please submit an application.',
                    ])
                );
            }

            // Not a creator
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, "Designer profile not found. You need to be a creator (creator=1) to access designer features.")
            );

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(401, false, "Invalid or expired token: " . $e->getMessage())
            );
        }
    }


    /**
     * Submit new design
     */
    /**
     * Submit new design
     * Works with both admin tokens and app user tokens
     */
    public function submitDesign(Request $request)
    {
        // Get designer profile using same logic as getProfile
        $token = $request->bearerToken();

        if (!$token) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(401, false, "Authentication token required")
            );
        }

        try {
            $decoded = \App\Helpers\JwtHelper::decode($token);
            $uid = $decoded->uid ?? null;
            $email = $decoded->email ?? null;
            $userId = $decoded->id ?? $decoded->user_id ?? null;

            $profile = null;
            $isAdminToken = $userId && !$uid;

            if ($isAdminToken) {
                // Admin token - get profile directly
                $profile = DesignerProfile::where('user_id', $userId)->first();
            } else {
                // App user token - find user in user_data, then check for profile
                $userData = null;

                if ($uid) {
                    $userData = \App\Models\UserData::where('uid', $uid)->first();
                }

                if (!$userData && $email) {
                    $userData = \App\Models\UserData::where('email', $email)->first();
                }

                if (!$userData) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(404, false, "User not found. Please login through the mobile app.")
                    );
                }

                // Check if creator
                if ($userData->creator != 1) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(403, false, "Only creators can submit designs. Please apply to become a creator.")
                    );
                }

                // Find matching user in users table and get profile
                $user = \App\Models\User::where('email', $userData->email)->first();
                if ($user) {
                    $profile = DesignerProfile::where('user_id', $user->id)->first();
                }
            }

            if (!$profile) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, "Designer profile not found. You need an approved designer profile to submit designs.")
                );
            }

            if (!$profile->is_active) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(403, false, "Your designer account is inactive")
                );
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|in:template,video,sticker,frame,gif,vector,bg',
                'category_id' => 'nullable|integer',
                'design_file' => 'required|file|mimes:jpg,jpeg,png,pdf,ai,psd,svg,mp4|max:51200',
                'preview_images' => 'nullable|array',
                'preview_images.*' => 'file|mimes:jpg,jpeg,png|max:5120',
                'tags' => 'nullable|array',
                'tags.*' => 'string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'keywords' => 'nullable|array',
                'keywords.*' => 'string',
            ]);

            if ($validator->fails()) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(422, false, "Validation failed", [
                        'errors' => $validator->errors()
                    ])
                );
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

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(201, true, "Design submitted successfully! It will be reviewed by the designer head.", [
                    'design' => [
                        'id' => $design->id,
                        'status' => $design->status,
                        'title' => $design->title,
                        'category' => $design->category,
                    ]
                ])
            );

        } catch (\Exception $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(401, false, "Invalid or expired token: " . $e->getMessage())
            );
        }
    }


    /**
     * Get designer's designs
     */
    public function getDesigns(Request $request)
    {
        $result = $this->getDesignerProfileFromToken($request);

        if (isset($result['error'])) {
            return $result['error'];
        }

        $profile = $result['profile'];

        $status = $request->input('status');
        $perPage = $request->input('per_page', 20);

        $query = DesignSubmission::where('designer_id', $profile->id)
            ->with(['seoDetails']);

        if ($status) {
            $query->where('status', $status);
        }

        $designs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, "Designs retrieved successfully", [
                'designs' => $designs->map(function ($design) {
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
            ])
        );
    }

    /**
     * Get single design details
     */
    public function getDesignDetails($id, Request $request)
    {
        $result = $this->getDesignerProfileFromToken($request);

        if (isset($result['error'])) {
            return $result['error'];
        }

        $profile = $result['profile'];

        $design = DesignSubmission::with(['seoDetails', 'sales'])
            ->where('id', $id)
            ->where('designer_id', $profile->id)
            ->first();

        if (!$design) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, "Design not found")
            );
        }

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, "Design details retrieved successfully", [
                'design' => [
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
            ])
        );
    }
}
