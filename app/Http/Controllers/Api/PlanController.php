<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Utils\ContentManager;
use App\Models\Draft;
use App\Models\OfferPopUp;
use App\Models\Pricing\PaymentConfiguration;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\Plan;
use App\Models\Pricing\PlanDuration;
use App\Models\Pricing\PlanFeature;
use App\Models\Pricing\PlanUserDiscount;
use App\Models\Pricing\SubPlan;
use App\Models\PromoCode;
use App\Models\TransactionLog;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends ApiController
{

    public function getPlanData2(Request $request): mixed
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $ipData = HelperController::getIpAndCountry($request);
        $currency = strtoupper($ipData['cur']);

        $planDurations = PlanDuration::select(['id', 'name', 'duration', 'is_annual'])->get();
        $plans = Plan::where('status', 1)->get();

        $planIds = $plans->pluck('string_id')->toArray();
        $subPlans = SubPlan::whereIn('plan_id', $planIds)->where('deleted', 0)->get();

        $featureIds = [];
        foreach ($plans as $plan) {
            foreach ($plan->appearance as $item) {
                if (isset($item['features_id']) && !array_key_exists($item['features_id'], $item)) {
                    $featureIds[] = $item['features_id'];
                }
            }
        }

        $features = PlanFeature::whereIn('id', $featureIds)->get()->keyBy('id');

        $subPlansByPlanId = $subPlans->groupBy('plan_id');

        $plansData = $plans->map(function ($plan) use ($currency, $planDurations, $features, $subPlansByPlanId) {

            $appearance = [];

            foreach ($plan->appearance as $item) {
                $metaAppearance = $item['is_feature_visible'] ?? false;
                if (!$metaAppearance) continue;

                $featureName = null;

                if (isset($item['features_id'])) {
                    $featureName = $features[$item['features_id']]->name ?? null;
                }

                $metaAppearance = $item['meta_appearance'] ?? null;
                $appearance[] = [
                    'meta_appearance' => $metaAppearance ?: $featureName,
                    'meta_value' => $item['meta_value'] ?? null,
                    'sub_name' => $item['sub_name'] ?? null,
                ];

            }
            $appearance = collect($appearance)->sortByDesc('meta_value')->values()->all();

            $subPlanData = [];
            if (isset($subPlansByPlanId[$plan->string_id])) {
                $subPlanData = $subPlansByPlanId[$plan->string_id]->map(function ($subPlan) use ($currency, $planDurations) {
                    $planDetails = $subPlan->plan_details;

                    $duration = $planDurations->where('id', $subPlan->duration_id)->first();
                    $durationValue = $duration->duration ?? 0;
                    $additionalDuration = (int)($planDetails['additional_duration'] ?? 0);
                    $totalDay = $durationValue + $additionalDuration;

                    $isInr = $currency === 'INR';
                    $curSymbol = $isInr ? '₹' : '$';
                    $key_price = $isInr ? 'inr_price' : 'usd_price';
                    $key_offer_price = $isInr ? 'inr_offer_price' : 'usd_offer_price';

                    $price = (float)($planDetails[$key_price] ?? 0);
                    $finalPrice = (float)($planDetails[$key_offer_price] ?? 0);

                    $disc = (($price - $finalPrice) / $price) * 100;
                    $discount = $disc;

                    $finalPrice = (float)number_format($finalPrice, 2, '.', '');

                    return [
                        'id' => $subPlan->id,
                        'string_id' => $subPlan->string_id,
                        'plan_id' => $subPlan->plan_id,
                        'duration_id' => (int)$subPlan->duration_id,
                        'details' => [
                            'additional_duration' => $additionalDuration,
                            'duration' => $durationValue,
                            'total_day' => $totalDay,
                            'price' => $curSymbol . $price,
                            'discount' => number_format($discount, 2) . '%',
                            'final_price' => $curSymbol . $finalPrice,
                            'amount' => $finalPrice,
                            'base_price' => '₹' . $price,
                            'one_user_price' => '₹' . number_format($finalPrice, 2, '.', ''),
                            'discount_price' => $curSymbol.$finalPrice,
                            'additional_user_discount' => "0%",
                            'additional_charge' => null,
                            'total_discount' => $discount . '%',
                            'total_price' => $curSymbol.$finalPrice,
                        ],
                    ];
                });
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'sub_title' => $plan->sub_title,
                'btn_name' => $plan->btn_name,
                'is_recommended' => $plan->is_recommended == 1,
                'is_free_type' => $plan->is_free_type == 1,
                'string_id' => $plan->string_id,
                'icon' => ContentManager::getStorageLink($plan->icon),
                'description' => $plan->description,
                'appearance' => $appearance,
                'sub_plan' => $subPlanData,
            ];
        });

        $userData = UserData::whereUid($this->uid)->first();

        $response = [
            'duration' => $planDurations,
            'plans' => $plansData,
            'ipData' => $ipData,
            'check_user_offer' => $userData?->email == 'viddhi.crafty@gmail.com'
        ];

        return $this->successed(msg: 'Plan data loaded successfully.', datas: $response);
    }

    public function getPlanData(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        // $ipData = HelperController::getIpAndCountry($request);
        // $currency = strtoupper($ipData['cur']);
        $currency = "INR";
        $formatPrice = function ($amount, $currencyText = ''): string {
            return $currencyText . number_format((float)$amount, 2, '.', '');
        };
        $planDurations = PlanDuration::select('id', 'name', 'duration', 'is_annual')->get();
        $plans = Plan::where('status', 1)->get();

        $planIds = $plans->pluck('string_id')->toArray();
        $subPlans = SubPlan::whereIn('plan_id', $planIds)->where('deleted', 0)->get();

        $featureIds = [];
        foreach ($plans as $plan) {
            foreach ($plan->appearance as $item) {
                if (isset($item['features_id']) && !array_key_exists($item['features_id'], $item)) {
                    $featureIds[] = $item['features_id'];
                }
            }
        }

        $features = PlanFeature::whereIn('id', $featureIds)->get()->keyBy('id');

        $subPlansByPlanId = $subPlans->groupBy('plan_id');

        $plansData = $plans->map(function ($plan) use ($formatPrice, $currency, $planDurations, $features, $subPlansByPlanId) {

            $appearance = [];

            foreach ($plan->appearance as $item) {
                $featureName = null;
                $metaAppearance = $item['is_feature_visible'] ?? false;

                if (!$metaAppearance)
                    continue;

                if (isset($item['features_id'])) {
                    $featureName = $features[$item['features_id']]->name ?? null;
                }

                $metaAppearance = $item['meta_appearance'] ?? null;
                $appearance[] = [
                    'meta_appearance' => $metaAppearance ?: $featureName,
                    'meta_value' => $item['meta_value'] ?? 0,
                    'sub_name' => $item['sub_name'] ?? null
                ];
            }

            $appearance = collect($appearance)->sortByDesc('meta_value')->values()->all();
//            $appearance = collect($appearance)
//                ->sortBy([
//                    ['meta_value', 'desc'],      // First sort by meta_value (highest first)
//                    ['feature_order', 'asc'],    // Then sort by order (lowest first)
//                ])
//                ->values()
//                ->all();

            $subPlanData = [];
            if (isset($subPlansByPlanId[$plan->string_id])) {
                $subPlanData = $subPlansByPlanId[$plan->string_id]->map(function ($subPlan) use ($currency, $planDurations,$formatPrice) {
                    $planDetails = $subPlan->plan_details;

                    $duration = $planDurations->where('id', $subPlan->duration_id)->first();
                    $durationValue = $duration->duration ?? 0;
                    $additionalDuration = (int) ($planDetails['additional_duration'] ?? 0);
                    $totalDay = $durationValue + $additionalDuration;
                    $currencyText = $currency === 'INR' ? '₹' : '$';
                    $key_price = $currency === 'INR' ? 'inr_price' : 'usd_price';
                    $key_offer_price = $currency === 'INR' ? 'inr_offer_price' : 'usd_offer_price';
                    $key_discount = $currency === 'INR' ? 'inr_discount' : 'usd_discount';

                    $price = (float) ($planDetails[$key_price] ?? 0);
                    $finalPrice = (float) ($planDetails[$key_offer_price] ?? 0);
                    $discount = (float) ($planDetails[$key_discount] ?? 0);
                    $discountedPrice =  $price - $finalPrice;
                    return [
                        'id' => $subPlan->id,
                        'string_id' => $subPlan->string_id,
                        'plan_id' => $subPlan->plan_id,
                        'duration_id' => (int) $subPlan->duration_id,
                        'details' => [
                            'additional_duration' => $additionalDuration,
                            'duration' => $durationValue,
                            'total_day' => $totalDay,
                            'price' => '₹' . $price,
                            'discount' => $discount . '%',
                            'final_price' => '₹' . number_format($finalPrice, 2, '.', ''),
                            'base_price' => '₹' . $price,
                            'one_user_price' => '₹' . number_format($finalPrice, 2, '.', ''),
                            'discount_price' => $formatPrice($finalPrice, $currencyText),
                            'additional_user_discount' => "0%",
                            'additional_charge' => null,
                            'total_discount' => $discount . '%',
                            'total_price' => $formatPrice($finalPrice, $currencyText),
                            'amount' => $finalPrice,
                        ],
                    ];
                });
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'sub_title' => $plan->sub_title,
                'btn_name' => $plan->btn_name,
                'is_recommended' => $plan->is_recommended == 1,
                'is_free_type' => $plan->is_free_type == 1,
                'string_id' => $plan->string_id,
                'icon' => ContentManager::getStorageLink($plan->icon),
                'description' => $plan->description,
                'appearance' => $appearance,
                'sub_plan' => $subPlanData,
            ];
        });

        $response = [
            'duration' => $planDurations,
            'plans' => $plansData,
        ];

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, 'Plan data loaded successfully.', $response)
        );
    }

    private function finalResponse($req, $code, $msg, $access = false, $extra = []): array|string
    {
        return ResponseHandler::sendResponse(
            $req,
            new ResponseInterface($code, $code === 200, $msg, array_merge(
                [
                    'isAccessTemplate' => $access,
                    "data" => $extra
                ],
            ))
        );
    }

    private function limitResponse($req, $limit, $type, $used, $planDetails = []): array|string
    {
        $canAccess = $used < $limit;
        $msg = $canAccess ? 'Access allowed' : "Your $type limit is expired";

        return $this->finalResponse($req, 200, $msg, $canAccess, [
            "data" => $planDetails,
        ]);
    }

    public function getAdditionalUserPlan(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $formatPrice = function ($amount, $currencyText = ''): string {
            return $currencyText . number_format((float)$amount, 2, '.', '');
        };

        $planId = $request->input('plan_id');
        $numUsers = (int)$request->input('number_of_user', 1);
        $currency = $request->input('currency', 'INR');

        $currency = strtoupper($currency);

        $planDiscount = PlanUserDiscount::first();

        $discountPercentage = null;
        $additionalDiscountPercentage = null;

        if ($numUsers > 1) {
            $discountPercentage = $planDiscount?->discount_percentage ?? 0;
            $additionalDiscountPercentage = $discountPercentage . '%';
        }

        $subPlans = SubPlan::where('plan_id', $planId)
            ->where('deleted', 0)
            ->get();

        $durations = PlanDuration::whereIn('id', $subPlans->pluck('duration_id')->unique())
            ->get()
            ->keyBy('id');

        $subPlanData = $subPlans->map(function ($subPlan) use ($currency, $numUsers, $discountPercentage, $additionalDiscountPercentage, $durations, $formatPrice) {
            $planDetails = $subPlan->plan_details;

            $duration = $durations[$subPlan->duration_id] ?? null;
            $durationValue = (int)($duration?->duration ?? 0);

            $additionalDuration = (int)($planDetails['additional_duration'] ?? 0);
            $totalDay = $durationValue + $additionalDuration;

            $details = [
                'additional_duration' => $additionalDuration,
                'duration' => $durationValue,
                'total_day' => $totalDay,
            ];

            $isInr = $currency === 'INR';

            $currencyText = $isInr ? '₹' : '$';
            $key_price = $currency === 'INR' ? 'inr_price' : 'usd_price';
            $key_offer_price = $currency === 'INR' ? 'inr_offer_price' : 'usd_offer_price';
            $key_discount = $currency === 'INR' ? 'inr_discount' : 'usd_discount';

            $basePrice = (float)($planDetails[$key_price] ?? 0);
            $subPlanDiscount = (float)($planDetails[$key_discount] ?? 0);

            // Step 1: Apply plan discount for 1 user
            $oneUserPrice = $basePrice;

            $discountedPrice = (float)($planDetails[$key_offer_price] ?? 0);

            // Step 2: Apply additional discount if multiple users
            $additionalCharge = null;
            $totalPrice = $discountedPrice;

            if ($numUsers > 1) {
                $extraUsers = $numUsers - 1;

                $additionalDiscount = $discountPercentage > 0 ? ($discountedPrice * $discountPercentage / 100) : 0;
                $discountedExtraPrice = $discountedPrice - $additionalDiscount;

                $additionalCharge = $discountedExtraPrice * $extraUsers;
                $totalPrice = $discountedPrice + $additionalCharge;
            }

            // Step 3: Total calculations
            $baseTotalPrice = $oneUserPrice * $numUsers;
            $totalDiscountPercent = 100 - (($totalPrice / $baseTotalPrice) * 100);

            // Step 4: Format results
            $details['discount'] = $subPlanDiscount . '%';
            $details['base_price'] = $formatPrice($baseTotalPrice, $currencyText);
            $details['one_user_price'] = $formatPrice($oneUserPrice, $currencyText);
            $details['discount_price'] = $formatPrice($discountedPrice, $currencyText);
            $details['additional_user_discount'] = $numUsers > 1 ? $additionalDiscountPercentage : null;
            $details['additional_charge'] = $numUsers > 1 ? $formatPrice($additionalCharge, $currencyText) : null;
            $details['total_discount'] = number_format($totalDiscountPercent, 0) . '%';
            $details['total_price'] = $formatPrice($totalPrice, $currencyText);
            $details['amount'] = $totalPrice;

            return [
                'id' => $subPlan->id,
                'string_id' => $subPlan->string_id,
                'plan_id' => $subPlan->plan_id,
                'duration_id' => (int)$subPlan->duration_id,
                'details' => $details,
            ];
        });

        $response = [
            'number_of_user' => $numUsers,
            'sub_plan' => $subPlanData,
        ];

        return $this->successed(datas: $response);
    }


//    public function getAdditionalUserPlan(Request $request)
//    {
//        $formatPrice = function ($amount, $currencyText = ''): string {
//            return $currencyText . number_format((float) $amount, 2, '.', '');
//        };
//        $planId = $request->plan_id;
//        $numUsers = (int) $request->number_of_user;
//        $currency = strtoupper($request->input('currency', 'INR'));
//        $planDiscount = PlanUserDiscount::first();
//        $discountPercentage = null;
//        $additionalDiscountPercentage = null;
//        if ($numUsers > 1) {
//            $discountPercentage = $planDiscount?->discount_percentage ?? 0;
//            $additionalDiscountPercentage = $discountPercentage . '%';
//        }
//        $subPlans = SubPlan::where('plan_id', $planId)
//            ->where('deleted', 0)
//            ->get();
//        $durations = PlanDuration::whereIn('id', $subPlans->pluck('duration_id')->unique())
//            ->get()
//            ->keyBy('id');
//        $subPlanData = $subPlans->map(function ($subPlan) use ($currency, $numUsers, $discountPercentage, $additionalDiscountPercentage, $durations, $formatPrice) {
//            $planDetails = $subPlan->plan_details;
//            $duration = $durations[$subPlan->duration_id] ?? null;
//            $durationValue = (int) ($duration?->duration ?? 0);
//            $additionalDuration = (int) ($planDetails['additional_duration'] ?? 0);
//            $totalDay = $durationValue + $additionalDuration;
//            $details = [
//                'additional_duration' => $additionalDuration,
//                'duration' => $durationValue,
//                'total_day' => $totalDay,
//            ];
//            $currencyText = $currency === 'INR' ? '₹' : '$';
//            $key_price = $currency === "INR" ? 'inr_price' : 'usd_price';
//            $key_discount = $currency === "INR" ? 'inr_discount' : 'usd_discount';
//            $basePrice = (float) ($planDetails[$key_price] ?? 0);
//            $subPlanDiscount = (float) ($planDetails[$key_discount] ?? 0);
//            // Step 1: Apply plan discount for 1 user
//            $oneUserPrice = $basePrice;
//            $discountAmount = ($oneUserPrice * $subPlanDiscount) / 100;
//            $discountedPrice = $oneUserPrice - $discountAmount;
//            // Step 2: Apply additional discount if multiple users
//            $additionalCharge = null;
//            $totalPrice = $discountedPrice;
//            if ($numUsers > 1) {
//                $extraUsers = $numUsers - 1;
//                $additionalDiscount = $discountPercentage > 0 ? ($discountedPrice * $discountPercentage / 100) : 0;
//                $discountedExtraPrice = $discountedPrice - $additionalDiscount;
//                $additionalCharge = $discountedExtraPrice * $extraUsers;
//                $totalPrice = $discountedPrice + $additionalCharge;
//            }
//            // Step 3: Total calculations
//            $baseTotalPrice = $oneUserPrice * $numUsers;
//            $totalDiscountPercent = 100 - (($totalPrice / $baseTotalPrice) * 100);
//            // Step 4: Format results
//            $details['discount'] = $subPlanDiscount . '%';
//            $details['base_price'] = $formatPrice($baseTotalPrice, $currencyText);
//            $details['one_user_price'] = $formatPrice($oneUserPrice, $currencyText);
//            $details['discount_price'] = $formatPrice($discountedPrice, $currencyText);
//            $details['additional_user_discount'] = $numUsers > 1 ? $additionalDiscountPercentage : null;
//            $details['additional_charge'] = $numUsers > 1 ? $formatPrice($additionalCharge, $currencyText) : null;
//            $details['total_discount'] = number_format($totalDiscountPercent, 0) . '%';
//            $details['total_price'] = $formatPrice($totalPrice, $currencyText);
//            return [
//                'id' => $subPlan->id,
//                'string_id' => $subPlan->string_id,
//                'plan_id' => $subPlan->plan_id,
//                'duration_id' => (int) $subPlan->duration_id,
//                'details' => $details,
//            ];
//        });
//        $response = [
//            'number_of_user' => $numUsers,
//            'sub_plan' => $subPlanData,
//        ];
//        return ResponseHandler::sendResponse(
//            $request,
//            new ResponseInterface(200, true, 'Sub plan data loaded successfully.', $response)
//        );
//    }

    public function checkSubscriptionDetails(Request $request){
        $subPlanID = $request->get('id');
        $currency = $request->get('currency');
        $subPlan = SubPlan::whereId($subPlanID)->first();
        if(!$subPlan){
            return ResponseHandler::sendResponse($request,new ResponseInterface(200, false,"Sub Plan Not Found"));
        }

        $data = $this->checkSubscriptionIdBySubPlan(subPlan: $subPlan,request: $request,currency: $currency);

        return ResponseHandler::sendResponse($request,new ResponseInterface(200,true,"Data Fetched",["data" => $data]));

    }

//    public function checkSubscriptionIdBySubPlan(SubPlan $subPlan,Request $request,$currency = "INR"): array
//    {
//        $subscriptionIds = $subPlan->subscription_ids;
//        if ($currency == "INR"){
//            $paymentScope = "NATIONAL";
//        } else {
//            $paymentScope = "INTERNATIONAL";
//        }
//        $paymentConfig = PaymentConfiguration::wherePaymentScope($paymentScope)->whereIsActive(1)->first();
//        if(!$paymentConfig){
//            return ResponseHandler::sendResponse($request,new ResponseInterface(200,false,"Payment Configuration not found"));
//        }
//        $isSubscriptionAvail = array_key_exists($paymentConfig->gateway,$subscriptionIds);
//        if($isSubscriptionAvail) {
//            $data['subscriptionId'] = $subscriptionIds[$paymentConfig->gateway];
//        }
//        $data['isSubscriptionAvail'] = $isSubscriptionAvail;
//        $data['paymentConfig']['gateway'] = $paymentConfig->gateway;
//        $data['paymentConfig']['credentials'] = $paymentConfig->credentials;
//        $data['paymentConfig']['status'] = $paymentConfig->is_active;
//






//        return $data;
//    }

    public function checkSubscriptionIdBySubPlan(
        SubPlan $subPlan,
        Request $request,
                $currency = "INR"
    ): array {
        // Decode subscription IDs (JSON → array)
        $subscriptionIds = $subPlan->subscription_ids;

        $paymentScope = ($currency === "INR") ? "NATIONAL" : "INTERNATIONAL";

        /** -------------------------
         * 1️⃣ Get ACTIVE payment config
         * --------------------------*/
        $activePaymentConfig = PaymentConfiguration::wherePaymentScope($paymentScope)
            ->whereIsActive(1)
            ->first();

        if (!$activePaymentConfig) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, false, "Payment Configuration not found")
            );
        }

        if (array_key_exists($activePaymentConfig->gateway, $subscriptionIds)) {
            return [
                'isSubscriptionAvail' => true,
                'subscriptionId'      => $subscriptionIds[$activePaymentConfig->gateway],
                'paymentConfig'       => [
                    'gateway'     => $activePaymentConfig->gateway,
                    'credentials' => $activePaymentConfig->credentials,
                    'status'      => $activePaymentConfig->is_active,
                ],
            ];
        }

        $inactivePaymentConfigs = PaymentConfiguration::wherePaymentScope($paymentScope)
            ->whereIsActive(0)
            ->get();

        foreach ($inactivePaymentConfigs as $inactiveConfig) {
            if (array_key_exists($inactiveConfig->gateway, $subscriptionIds)) {
                return [
                    'isSubscriptionAvail' => true,
                    'subscriptionId'      => $subscriptionIds[$inactiveConfig->gateway],
                    'paymentConfig'       => [
                        'gateway'     => $inactiveConfig->gateway,
                        'credentials' => $inactiveConfig->credentials,
                        'status'      => $inactiveConfig->is_active,
                    ],
                ];
            }
        }

        return [
            'isSubscriptionAvail' => false,
            'paymentConfig'       => [
                'gateway'     => $activePaymentConfig->gateway,
                'credentials' => $activePaymentConfig->credentials,
                'status'      => $activePaymentConfig->is_active,
            ],
        ];
    }


    public function getOfferPackage(Request $request)
    {
        $currency = strtoupper($request->input('currency', 'USD'));

        $bonusPackage = OfferPackage::with(['plan', 'duration', 'BonusPackage'])
            ->where('status', 1)
            ->first();

        if (!$bonusPackage) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, 'No active bonus package found.')
            );
        }

        $plan = Plan::with(['subPlans.duration'])
            ->where('string_id', $bonusPackage->plan_id)
            ->firstOrFail();

        $subPlan = SubPlan::with('duration')
            ->where('string_id', $bonusPackage->sub_plan_id)
            // ->where('status', 1)
            ->first();

        if (!$subPlan) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, 'No active subplan found for this plan.')
            );
        }

        $planDetails = json_decode($subPlan->plan_details, true);
        $duration = $subPlan->duration;
        $durationValue = $duration?->duration ?? 0;

        $additionalDuration = (int) ($planDetails['additional_duration'] ?? 0);
        $totalDay = $durationValue + $additionalDuration;

        $currencyText = $currency === 'INR' ? "₹" : "$";
        $key_price = 'inr_price';
        $key_discount = 'inr_discount';

        if ($currency !== "INR") {
            $key_price = 'usd_price';
            $key_discount = 'usd_discount';
        }

        $basePrice = (float) ($planDetails[$key_price] ?? 0);
        $subPlanDiscount = (float) ($planDetails[$key_discount] ?? 0);
        $bonusPrice = $bonusPackage['BonusPackage'][$key_price];

        $discountAmount = ($basePrice * $subPlanDiscount) / 100;
        $discountedPrice = $basePrice - $discountAmount;

        $planWithBonusPrice = $discountedPrice + $bonusPrice;
        $planWithBonusDuration = $totalDay + ($bonusPackage->BonusPackage->additional_day ?? 0);

        $response = [
            'offer_package' => [
                'id' => $bonusPackage->id,
                'string_id' => $bonusPackage->string_id,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'string_id' => $plan->string_id,
                    'status' => $plan->status,
                    'duration' => [
                        'id' => $duration->id ?? null,
                        'name' => $duration->name ?? null,
                        'duration' => $durationValue,
                    ],
                    'sub_plan' => [
                        'id' => $subPlan->id,
                        'string_id' => $subPlan->string_id,
                        'duration_id' => $subPlan->duration_id,
                        'details' => [
                            'additional_duration' => $additionalDuration,
                            'duration' => $durationValue,
                            'total_day' => $totalDay,
                            'discount' => $subPlanDiscount . '%',
                            'base_price' => $currencyText . number_format($basePrice, 2),
                            'discount_amount' => $currencyText . number_format($discountAmount, 2),
                            'discount_price' => $currencyText . number_format($discountedPrice, 2),
                        ]
                    ],
                ],
                'bonus_package' => [
                    'bonus_code' => $bonusPackage->BonusPackage->bonus_code ?? null,
                    'price' => $currencyText . number_format($bonusPrice, 2),
                    'additional_day' => $bonusPackage->BonusPackage->additional_day ?? null,
                ],
                'plan_with_bonus' => [
                    'price' => $currencyText . number_format($planWithBonusPrice, 2),
                    'duration' => $planWithBonusDuration,
                ]
            ],
        ];

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, 'Active plan with bonus package loaded successfully.', $response)
        );
    }

    // is_template
    public function is_template(Request $request): array|string
    {
        $user = UserData::where('uid', $request->uid)->first();
        if (!$user) {
            return $this->finalResponse($request, 404, 'User not found');
        }

        $log = TransactionLog::where('user_id', $user->uid)->latest()->first();
        if (!$log) {
            return $this->finalResponse($request, 404, 'No transaction found');
        }

        if ($log->type == "0") {
            return $this->finalResponse($request, 200, 'Access allowed', true);
        } else if ($log->type == "1") {
            $subPlanId = SubPlan::where('string_id', $log->plan_id)->value('plan_id');
            if (!$subPlanId) {
                return $this->finalResponse($request, 404, 'Subplan not found');
            }
        } else {
            $offerPackageId = OfferPackage::where('string_id', $log->plan_id)->value("sub_plan_id");
            if (!$offerPackageId) {
                return $this->finalResponse($request, 404, 'Offer Package not found');
            }
            $subPlanId = SubPlan::where('string_id', $offerPackageId)->value('plan_id');
            if (!$subPlanId) {
                return $this->finalResponse($request, 404, 'Subplan not found');
            }
        }

        $plan = Plan::where('string_id', $subPlanId)->first();
        if (!$plan) {
            return $this->finalResponse($request, 404, 'Plan not found');
        }

        $slug = $request->slug;
        $slugMap = ['is_template', 'is_caricature'];

        if (!in_array($slug, $slugMap, true)) {
            return $this->finalResponse($request, 400, 'Invalid slug parameter');
        }

        $daysLeft = $log->expired_at ? now()->diffInDays($log->expired_at, false) : null;

        $planDetails = [
            'plan_name' => $plan->name,
            'device_limit' => $user->device_limit,
            'duration_type' => null,
            'feature_limit' => null,
            'feature_used' => null,
            'days_left' => $daysLeft,
            'plan_started_at' => $log->created_at,
            'plan_expiry_date' => $log->expired_at,
        ];

        if ($log->expired_at && now()->gt($log->expired_at)) {
            return $this->finalResponse($request, 200, 'Plan is expired', false);
        }

        $appearance = json_decode($log->plan_limits, true);
        $featureConfig = collect($appearance)->firstWhere('slug', $slug);

        if (!$featureConfig) {
            return $this->finalResponse($request, 200, 'Feature config not found', true, $planDetails);
        }

        $isDaily = $featureConfig['is_daily'];
        $limit = (int) $isDaily ? $featureConfig['daily'] : $featureConfig['monthly'];
        $type = $isDaily ? "Daily" : "Monthly";

        $planDetails['duration_type'] = $type;
        $planDetails['feature_limit'] = $limit;

        $query = Draft::where('user_id', $user->uid);

        if ($limit === -1) {
            $planDetails['feature_used'] = $query->count();
            return $this->finalResponse($request, 200, 'Unlimited access', true, $planDetails);
        }

        if ($isDaily) {
            $used = $query->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->count();
            $planDetails['feature_used'] = $used;
            return $this->limitResponse($request, $limit, $type, $used, $planDetails);
        } else {
            $start = Carbon::parse($log->created_at);
            $end = Carbon::parse($log->expired_at ?? now());

            $cycleStart = $start->copy();
            while ($cycleStart->addMonth() <= now() && $cycleStart < $end) {
                $start = $cycleStart->copy();
            }

            $cycleEnd = $start->copy()->addMonth();
            if ($cycleEnd > $end) {
                $cycleEnd = $end;
            }

            $used = $query->whereBetween('created_at', [$start, $cycleEnd])->count();
            $planDetails['feature_used'] = $used;
            return $this->limitResponse($request, $limit, $type, $used, $planDetails);
        }

//        $planDetails['feature_used'] = $query->count();
//        return $this->finalResponse($request, 200, 'Access allowed', true, $planDetails);
    }

    public function setPlanLimitBySubPlanId(Request $request): array|string
    {

        $type = "2";

        $id = $request->id;

        if ($type == "1") {
            $subPlan = SubPlan::with('plan')->find($id);

            if (!$subPlan) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'Sub Plan not found.')
                );
            }

            $appearance = $subPlan['plan']['appearance'] ?? [];

            $appearanceData = is_string($appearance)
                ? json_decode($appearance, true)
                : $appearance;

        } elseif ($type == "2") {
            $offerPackage = OfferPackage::with('plan')->find($id);

            if (!$offerPackage) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'No bonus package found.')
                );
            }

            $appearance = $offerPackage['plan']['appearance'] ?? [];

            $appearanceData = is_string($appearance)
                ? json_decode($appearance, true)
                : $appearance;
        }
        $appearance = [];
        if ($appearanceData) {
            foreach ($appearanceData as $item) {
                $subName = $item['sub_name'] ?? null;
                $metaFeatureValue = $item['meta_feature_value'] ?? [];
                $slug = $item['slug'] ?? null;
                $metaValue = $item['meta_value'] ?? null;

                if (!empty($slug) && $metaValue == 1 && !empty($subName)) {
                    $appearance[] = [
                        'sub_name' => $subName,
                        'slug' => $slug,
                        "monthly" => isset($metaFeatureValue[1]) && $metaFeatureValue[1] == "daily" ? 0 : ($metaFeatureValue[0] ?? 0),
                        "daily" => isset($metaFeatureValue[1]) && $metaFeatureValue[1] == "daily" ? ($metaFeatureValue[0] ?? 0) : 0,
                        "is_daily" => isset($metaFeatureValue[1]) && $metaFeatureValue[1] == "daily",
                        'meta_value' => $metaValue,
                    ];
                }
            }
        }

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, 'Appearance Data', [
                'appearance' => $appearance
            ])
        );
    }

    public function planOrOffer(Request $request)
    {
        $type = (int) $request->type;

        if (!in_array($type, [0, 1])) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(400, false, 'Invalid request type provided.')
            );
        }


        $currency = strtoupper($request->input('currency', 'USD'));
        $stringId = $request->string_id;
        $type = (int) $request->type;

        $subPlan = SubPlan::with('duration')
            ->where('string_id', $stringId)
            // ->where('status', 1)
            ->first();

        if (!$subPlan) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, 'Subplan not found.')
            );
        }

        // Common Plan Details
        $planDetails = json_decode($subPlan->plan_details, true) ?? [];
        $duration = $subPlan->duration;
        $durationValue = $duration?->duration ?? 0;
        $additionalDuration = (int) ($planDetails['additional_duration'] ?? 0);
        $totalDay = $durationValue + $additionalDuration;

        // Currency Wise Mapping
        $currencyText = $currency === 'INR' ? '₹' : '$';
        $price = (float) ($currency === 'INR' ? ($planDetails['inr_price'] ?? 0) : ($planDetails['usd_price'] ?? 0));
        $discount = (float) ($currency === 'INR' ? ($planDetails['inr_discount'] ?? 0) : ($planDetails['usd_discount'] ?? 0));

        // Plan
        if ($type === 0) {
            $plan = Plan::where('string_id', $subPlan->plan_id)
                ->where('status', 1)
                ->first();

            if (!$plan) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'Plan not found.')
                );
            }

            $totalPrice = number_format($price - (($price * $discount) / 100), 2, '.', '');

            $details = [
                'additional_duration' => $additionalDuration,
                'duration' => $durationValue,
                'total_day' => $totalDay,
                'price' => $currencyText . number_format($price, 2),
                'discount' => $discount . '%',
                'total_price' => $currencyText . $totalPrice,
            ];

            $response = [
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'string_id' => $plan->string_id,
                    'description' => $plan->description,
                    'sub_plan' => [
                        'id' => $subPlan->id,
                        'string_id' => $subPlan->string_id,
                        'duration_id' => $subPlan->duration_id,
                        'details' => $details
                    ]
                ]
            ];

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, 'Plan detail loaded successfully.', $response)
            );
        }

        // 🔹 Offer Package
        if ($type === 1) {
            $bonusPackage = OfferPackage::with('BonusPackage')
                ->where('sub_plan_id', $subPlan->string_id)
                ->first();

            if (!$bonusPackage) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'No bonus package found for this subplan.')
                );
            }

            $plan = Plan::where('string_id', $subPlan->plan_id)->first();

            $bonusPrice = (float) ($currency === 'INR'
                ? ($bonusPackage->BonusPackage->inr_price ?? 0)
                : ($bonusPackage->BonusPackage->usd_price ?? 0)
            );

            $discountAmount = ($price * $discount) / 100;
            $discountedPrice = $price - $discountAmount;

            $planWithBonusPrice = $discountedPrice + $bonusPrice;
            $planWithBonusDuration = $totalDay + ($bonusPackage->BonusPackage->additional_day ?? 0);

            $response = [
                'offer_package' => [
                    'id' => $bonusPackage->id,
                    'string_id' => $bonusPackage->string_id,
                    'plan' => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'string_id' => $plan->string_id,
                        'sub_plan' => [
                            'id' => $subPlan->id,
                            'string_id' => $subPlan->string_id,
                            'details' => [
                                'additional_duration' => $additionalDuration,
                                'duration' => $durationValue,
                                'total_day' => $totalDay,
                                'discount' => $discount . '%',
                                'base_price' => $currencyText . number_format($price, 2),
                                'discount_amount' => $currencyText . number_format($discountAmount, 2),
                                'discount_price' => $currencyText . number_format($discountedPrice, 2),
                            ]
                        ],
                    ],
                    'bonus_package' => [
                        'bonus_code' => $bonusPackage->BonusPackage->bonus_code ?? null,
                        'price' => $currencyText . number_format($bonusPrice, 2),
                        'additional_day' => $bonusPackage->BonusPackage->additional_day ?? null,
                    ],
                    'plan_with_bonus' => [
                        'price' => $currencyText . number_format($planWithBonusPrice, 2),
                        'duration' => $planWithBonusDuration,
                    ]
                ]
            ];

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, 'Offer package detail loaded successfully.', $response)
            );
        }

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(400, false, 'Invalid request type provided.')
        );
    }

    public function offerPopup(Request $request)
    {
        $offer = OfferPopUp::first();

        if (!$offer) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(404, false, 'No Offer Pop Up configured.', [])
            );
        }

        $promoCodes = PromoCode::where('id', $offer->promo_code)->first();

        $response = [
            'id' => $offer->id,
            'enable_offer' => (bool) $offer->enable_offer,
            'duration' => $offer->duration,
            'frequency_duration' => $offer->frequency_duration,
            'enable_force' => (bool) $offer->enable_force,
            'force_show_duration' => $offer->force_show_duration,
            'title' => $offer->title,
            'festival_name' => $offer->festival_name,
            'description' => $offer->description,
            'sub_description' => $offer->sub_description,
            'btn_name' => $offer->btn_name,
            'promo_code' => $promoCodes->promo_code,
            'expiry_date' => Carbon::parse($promoCodes->expiry_date)->format('F Y'),
            'disc' => $promoCodes->disc,
        ];

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, 'Offer Pop-Up detail loaded successfully.', $response)
        );
    }

}
