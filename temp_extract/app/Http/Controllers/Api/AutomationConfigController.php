<?php

namespace App\Http\Controllers\Api;

use App\Enums\ConfigType;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\AutomationUtils;
use App\Models\Config;
use App\Models\Design;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\SubPlan;
use App\Models\Subscription;
use App\Models\UserData;
use Illuminate\Http\Request;

class AutomationConfigController extends ApiController
{

    public function sendAutomationFromConfig(Request $request): array|string
    {
        $orderID = $request->order_id;
        $orders = Order::where('id', $orderID)->first();

        if (!$orders) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Order User not found"));
        }

        $type = $request->type;

        $userId = $orders->user_id;
        $contactNumber = $orders->contact_no;

        try {
            $user = UserData::where('uid', $userId)->first();
            if (!$user) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "User not found"));
            }

            $contactNumber = $contactNumber ?? $user->country_code . $user->number;

            $configType = ConfigType::from($type);
            $config = Config::where('name', $configType->label())->first();

            if (!$config) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Configuration not found"));
            }

            $configValue = $config->value;

            // Use Order model's method to get common data
            $commonData = $orders->getAutomationCommonData();

            if (isset($commonData['success']) && !$commonData['success']) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, $commonData['message']));
            }

            $results = [];

            if (in_array($type, [
                ConfigType::ACCOUNT_CREATE_AUTOMATION->value,
                ConfigType::CHECKOUT_DROP_AUTOMATION->value
            ])) {
                // Pre-fetch templates and promo codes for this single request
                $allTemplateData = AutomationUtils::preFetchAllTemplatesAndPromoCodes(
                    $configValue,
                    $type
                );

                if ($type == ConfigType::ACCOUNT_CREATE_AUTOMATION->value) {
                    $activeConfig = $configValue;
                } else {
                    $activeConfig = collect($configValue)->firstWhere('day', 0);
                    if (!$activeConfig) {
                        return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Day 0 configuration not found"));
                    }
                }

                $results = AutomationUtils::handleAutomationForJob(
                    $activeConfig,
                    $user,
                    $commonData,
                    $contactNumber,
                    $allTemplateData,
                    $type
                );

                return $this->formatFinalResponse($request, $results);
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "Config type not valid"));
        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, $e->getMessage()));
        }
    }

    private function formatFinalResponse($request, $results): array|string
    {
        $successCount = 0;
        $totalCount = 0;
        $messages = [];

        foreach ($results as $channel => $result) {
            $totalCount++;
            if ($result['success']) {
                $successCount++;
            }
            $messages[] = ucfirst($channel) . ": " . $result['message'];
        }

        $allSuccess = $successCount === $totalCount;
        $message = implode(' | ', $messages);

        if ($totalCount === 0) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "No automation channels enabled"));
        }

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface($allSuccess ? 200 : 207, $allSuccess, $message)
        );
    }
}