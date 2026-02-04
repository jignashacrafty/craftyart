<?php

namespace App\Http\Controllers\Api;
use App\Models\ContactUsWeb;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;


class ContactUsController extends ApiController
{
    public function contactUs(Request $request): array|string
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|email',
                'message'     => 'required|string',
                'system_info' => 'nullable|array',
            ]);

            $agent = new Agent();
            $systemInfo = [
                'ip'        => $request->ip(),
                'userAgent' => $request->header('User-Agent'),
                'device'    => $agent->device(),
                'platform'  => $agent->platform(),
                'browser'   => $agent->browser(),
                'desktop'   => $agent->isDesktop() ? 'Yes' : 'No',
                'mobile'    => $agent->isMobile() ? 'Yes' : 'No',
                'tablet'    => $agent->isTablet() ? 'Yes' : 'No',
            ];

            $frontendInfo = $request->input('system_info', []);
            if (!empty($frontendInfo)) {
                if (array_values($frontendInfo) === $frontendInfo) {
                    foreach ($frontendInfo as $item) {
                        if (is_array($item)) {
                            $systemInfo = array_merge($systemInfo, $item);
                        }
                    }
                } else {
                    $systemInfo = array_merge($systemInfo, $frontendInfo);
                }
            }

            // Save to DB
            ContactUsWeb::create([
                'user_id'      => $this->uid ?? null,
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'message'      => $validated['message'],
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->header('User-Agent'),
                'system_info'  => json_encode($systemInfo),
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, "Your Request Received, We will contact as soon as possible")
            );

        } catch (ValidationException $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(422, false, "Validation Failed", $e->errors())
            );
        } catch (\Exception $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, "Something went wrong, please try again later.", [
                    "error" => $e->getMessage()
                ])
            );
        }
    }
}
