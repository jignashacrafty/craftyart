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
            new ResponseInterface(201, true, "Application submitted successfully! We will review and get back to you soon.", [
                'application_id' => $application->id,
                'status' => $application->status,
            ])
        );
    }

    /**
     * Check application status (Public API)
     */
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(422, false, "Validation failed", [
                    'errors' => $validator->errors()->toArray()
                ])
            );
        }

        $application = DesignerApplication::where('email', $request->email)->first();

        if (!$application) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, "No application found with this email", [])
            );
        }

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, "Application found", [
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
                'reviewed_at' => $application->reviewed_at ? $application->reviewed_at->toISOString() : null,
                'rejection_reason' => $application->rejection_reason,
            ])
        );
    }
}
