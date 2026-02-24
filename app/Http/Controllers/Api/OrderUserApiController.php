<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\WebSocketBroadcastController;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\Api\ResponseInterface;
use App\Models\Order;
use App\Models\PersonalDetails;
use App\Models\ManageSubscription;
use App\Models\Pricing\OfferPackage;
use App\Models\Pricing\SubPlan;
use App\Models\Revenue\Sale;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserData;
use App\Models\Pricing\PaymentConfiguration;
use App\Models\PurchaseHistory;
use App\Services\PhonePeAutoPayService;
use App\Services\PhonePeTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderUserApiController extends AppBaseController
{
    const FOLLOWUP_LABELS = [
        'interested' => 'Interested',
        'highly_interested' => 'Highly Interested',
        'need_time' => 'Need Time',
        'call_cut' => 'Call Cut',
        'not_reachable' => 'Not Reachable',
        'switched_off' => 'Switched Off',
        'not_interested' => 'Not Interested',
        'personal_use_only' => 'Personal Use Only',
        'call_me_after_sometime' => 'Call Me After Sometime',
        'call_not_receive' => 'Call Not Receive',
        'active_plan' => 'Active Plan',
        'no_whatsapp_no_call' => 'No Whatsapp No Call',
    ];

    /**
     * Helper method to send encrypted response
     */
    private function sendResponse(Request $request, int $statusCode, bool $success, string $message, array $data = [])
    {
        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface($statusCode, $success, $message, $data)
        );
    }

    /**
     * Override parent constructor to remove global auth middleware
     * Auth is applied selectively via route middleware instead
     */
    public function __construct()
    {
        // Don't call parent::__construct() to avoid inheriting auth middleware
        // Auth is handled at route level for specific endpoints only
    }

    /**
     * GET /api/order-user
     * Get all orders with filters and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Order::query()
                ->with(['user'])
                ->where('is_deleted', 0)
                ->whereIn('status', ['failed', 'pending']);

            // Apply sales employee filter
            $isSalesEmployee = false;
            if ($request->header('Authorization')) {
                $user = auth('sanctum')->user();
                if ($user) {
                    $isSalesEmployee = RoleManager::isSalesEmployee($user->user_type);

                    if ($isSalesEmployee) {
                        $userId = $user->id;
                        $query->where(function ($q) use ($userId) {
                            $q->whereNull('emp_id')
                                ->orWhere('emp_id', 0)
                                ->orWhere('emp_id', $userId);
                        });
                    }
                }
            }

            // Filters
            $filterType = $request->get('filter_type', 'all');
            $statusFilter = $request->get('status_filter', 'all');
            $typeFilter = $request->get('type_filter');
            $amountSort = $request->get('amount_sort');
            $whatsappFilter = $request->get('whatsapp_filter');
            $emailFilter = $request->get('email_filter');
            $fromWhere = $request->get('from_where');
            $followupFilter = $request->get('followup_filter');
            $followupLabelFilter = $request->get('followup_label_filter');
            $searchTerm = $request->get('search');

            // Remove duplicates filter
            if ($filterType === 'remove_duplicate') {
                $query->whereIn('id', function ($sub) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('orders')
                        ->where('is_deleted', 0)
                        ->whereIn('status', ['failed', 'pending'])
                        ->groupBy('user_id', 'plan_id');
                });
            }

            // Status filter
            if (in_array($statusFilter, ['pending', 'failed'])) {
                $query->where('status', $statusFilter);
            }

            // Type filter
            if (in_array($typeFilter, ['old_sub', 'new_sub', 'template', 'video', 'caricature'])) {
                $query->where('type', $typeFilter);
            }

            // From where filter
            if (!empty($fromWhere)) {
                $query->where(function ($q) use ($fromWhere) {
                    if ($fromWhere === 'meta_google') {
                        $q->whereNotNull('fbc')
                            ->where(function ($qq) {
                                $qq->whereNotNull('gclid')
                                    ->orWhereNotNull('wbraid')
                                    ->orWhereNotNull('gbraid');
                            });
                    } elseif ($fromWhere === 'meta') {
                        $q->whereNotNull('fbc')
                            ->whereNull('gclid')
                            ->whereNull('wbraid')
                            ->whereNull('gbraid');
                    } elseif ($fromWhere === 'google') {
                        $q->whereNull('fbc')
                            ->where(function ($qq) {
                                $qq->whereNotNull('gclid')
                                    ->orWhereNotNull('wbraid')
                                    ->orWhereNotNull('gbraid');
                            });
                    } elseif ($fromWhere === 'seo') {
                        $q->whereNull('fbc')
                            ->whereNull('gclid')
                            ->whereNull('wbraid')
                            ->whereNull('gbraid');
                    }
                });
            }

            // Followup label filter
            if (!empty($followupLabelFilter) && array_key_exists($followupLabelFilter, self::FOLLOWUP_LABELS)) {
                $query->where('followup_label', $followupLabelFilter);
            }

            // WhatsApp, Email, Followup filters
            $filters = [
                'whatsapp_template_count' => $whatsappFilter,
                'email_template_count' => $emailFilter,
                'followup_call' => $followupFilter,
            ];

            foreach ($filters as $column => $filter) {
                match ($filter) {
                    'sent' => $query->where("$column", '>', 0),
                    'not_sent' => $query->where("$column", '<=', 0),
                    'called' => $query->where("$column", 1),
                    'not_called' => $query->where("$column", 0),
                    default => null,
                };
            }

            // Search
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('contact_no', 'LIKE', "%{$searchTerm}%");
                    });
                });
            }

            // Sorting
            $sortBy = in_array($amountSort, ['asc', 'desc']) ? 'amount' : 'id';
            $sortOrder = $amountSort ?: 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            // Format response
            $formattedOrders = $orders->map(function ($order) {
                // Handle plan_items - it's a Collection of objects
                $planItemsDisplay = '-';
                try {
                    $planItems = $order->plan_items;
                    if ($planItems && $planItems->isNotEmpty()) {
                        // Extract string_id or id_name from each item
                        $itemNames = $planItems->map(function ($item) {
                            if (is_object($item)) {
                                return $item->string_id ?? $item->id_name ?? $item->id ?? null;
                            }
                            return $item;
                        })->filter()->toArray();

                        $planItemsDisplay = !empty($itemNames) ? implode(', ', $itemNames) : $order->plan_id ?? '-';
                    } else {
                        $planItemsDisplay = $order->plan_id ?? '-';
                    }
                } catch (\Exception $e) {
                    $planItemsDisplay = $order->plan_id ?? '-';
                }

                return [
                    'id' => $order->id,
                    'user_name' => $order->user?->name ?? '-',
                    'email' => $order->user?->email ?? '-',
                    'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '-',
                    'amount' => $order->amount,
                    'amount_with_symbol' => $order->amount_with_symbol ?? '-',
                    'currency' => $order->currency ?? 'INR',
                    'type' => $order->type ?? '',
                    'plan_items' => $planItemsDisplay,
                    'followBy' => RoleManager::getUploaderName($order->emp_id),
                    'status' => $order->status,
                    'is_subscription_active' => $order->isSubscriptionActive(),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'email_template_count' => $order->email_template_count ?? 0,
                    'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                    'followup_call' => $order->followup_call ?? 0,
                    'followup_note' => $order->followup_note ?? '',
                    'followup_label' => $order->followup_label ?? null,
                    'from_where' => $order->from_where ?? '-',
                    'emp_id' => $order->emp_id ?? 0,
                    'user_id' => $order->user_id ?? null,
                ];
            });

            return $this->sendResponse($request, 200, true, "Orders fetched successfully", [
                'data' => $formattedOrders->toArray(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
                'followup_labels' => self::FOLLOWUP_LABELS,
            ]);

        } catch (\Exception $e) {
            Log::error('Order User API Index Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendResponse($request, 500, false, "Error fetching orders: " . $e->getMessage(), []);
        }
    }

    /**
     * POST /api/order-user/followup-update
     * Update followup status for an order
     * Requires authentication with encrypted token
     */
    public function followupUpdate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:orders,id',
                'followup_call' => 'required|integer|in:0,1',
                'followup_note' => 'nullable|string',
                'followup_label' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get authenticated user from middleware
            $currentUser = $request->get('authenticated_user');

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $orderUser = Order::find($request->id);

            if (!$orderUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Update followup
            if ($request->followup_call == 0) {
                $orderUser->followup_call = 0;
                $orderUser->followup_note = null;
                $orderUser->followup_label = null;
            } else {
                $orderUser->followup_call = 1;
                $orderUser->followup_note = $request->followup_note ?? '';
                $orderUser->followup_label = $request->followup_label;
            }

            // Set emp_id from authenticated user
            $orderUser->emp_id = $currentUser->id ?? 0;
            $orderUser->save();

            // Get employee name
            $followByName = RoleManager::getUploaderName($orderUser->emp_id);

            // Broadcast followup change
            WebSocketBroadcastController::broadcastOrderFollowUpChanged($orderUser);

            return response()->json([
                'success' => true,
                'message' => 'Followup updated successfully',
                'data' => [
                    'id' => $orderUser->id,
                    'followup_call' => $orderUser->followup_call,
                    'followup_note' => $orderUser->followup_note,
                    'followup_label' => $orderUser->followup_label,
                    'emp_id' => $orderUser->emp_id,
                    'follow_by' => $followByName,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Followup Update Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating followup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/order-user/get-user-usage
     * Get user usage type from personal_details
     */
    public function getUserUsage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|integer|exists:orders,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::find($request->order_id);

            if (!$order || !$order->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order or user not found'
                ], 404);
            }

            $personalDetails = PersonalDetails::where('uid', $order->user_id)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $order->user_id,
                    'usage' => $personalDetails->usage ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get User Usage Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching user usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/order-user/get-plans
     * Get subscription plans based on type
     */
    public function getPlans(Request $request): JsonResponse
    {
        try {
            $subscriptionType = $request->get('subscription_type', 'new_sub');
            $plans = [];

            if ($subscriptionType === 'new_sub') {
                $newPlans = SubPlan::where('deleted', 0)
                    ->with('plan')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($newPlans as $subPlan) {
                    $planDetails = is_string($subPlan->plan_details) ? json_decode($subPlan->plan_details, true) : $subPlan->plan_details;
                    $planName = $subPlan->plan->name ?? 'Plan ' . $subPlan->id;
                    $planPrice = $planDetails['inr_offer_price'] ?? $planDetails['inr_price'] ?? 0;

                    $durationText = '';
                    if ($subPlan->duration_id == 1) {
                        $durationText = ' (Monthly)';
                    } elseif ($subPlan->duration_id == 2) {
                        $durationText = ' (Yearly)';
                    } elseif ($subPlan->duration_id == 3) {
                        $durationText = ' (Lifetime)';
                    }

                    $plans[] = [
                        'id' => $subPlan->string_id ?? $subPlan->id,
                        'name' => $planName . $durationText,
                        'price' => $planPrice,
                        'type' => 'new_sub',
                        'duration_id' => $subPlan->duration_id,
                        'plan_details' => $planDetails,
                    ];
                }

            } elseif ($subscriptionType === 'old_sub') {
                $oldPlans = Subscription::where('status', 1)
                    ->orderBy('price', 'asc')
                    ->get();

                foreach ($oldPlans as $plan) {
                    $planName = $plan->package_name ?? $plan->desc ?? 'Plan ' . $plan->id;

                    if (strpos($planName, 'com.') === 0) {
                        $planName = ucwords(str_replace(['com.', '.'], ['', ' '], $planName));
                    }

                    $plans[] = [
                        'id' => $plan->id,
                        'name' => $planName . ' (' . $plan->validity . ' days)',
                        'price' => $plan->price ?? 0,
                        'type' => 'old_sub',
                        'validity' => $plan->validity,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $plans,
                'subscription_type' => $subscriptionType
            ]);

        } catch (\Exception $e) {
            Log::error('Get Plans Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading plans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/order-user/validate-email
     * Validate if email exists and user is active
     */
    public function validateEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->input('email');
            $user = UserData::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email not found in system',
                    'data' => [
                        'exists' => false,
                        'is_active' => false,
                    ]
                ]);
            }

            $isActive = ($user->status !== 0 && $user->status !== '0');

            return response()->json([
                'success' => true,
                'message' => $isActive ? 'Email verified - User is active' : 'User account is inactive',
                'data' => [
                    'exists' => true,
                    'is_active' => $isActive,
                    'user_id' => $user->id,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Email Validation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error validating email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/order-user/create-payment-link
     * Create payment link for Razorpay or PhonePe
     */
    public function createPaymentLink(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'contact_no' => 'required|string|max:15',
                'payment_method' => 'required|in:razorpay,phonepe',
                'plan_id' => 'required|string',
                'subscription_type' => 'required|in:old_sub,new_sub',
                'amount' => 'required|numeric|min:1',
                'plan_type' => 'required|in:personal,professional',
                'usage_type' => 'nullable|in:personal,professional',
                'caricature' => 'nullable|integer|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Validate email exists and user is active
            $user = UserData::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found in system. Please register first.'
                ], 400);
            }

            if ($user->status == 0 && $user->status !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive. Please activate the account first.'
                ], 400);
            }

            // Get authenticated user (sales person) from middleware
            $currentUser = $request->get('authenticated_user');

            // Generate reference ID
            $referenceId = Sale::generateReferenceId();

            // Create sale record
            $sale = Sale::create([
                'sales_person_id' => $currentUser ? $currentUser->id : 0,
                'user_name' => $validated['user_name'],
                'email' => $validated['email'],
                'contact_no' => $validated['contact_no'],
                'payment_method' => $validated['payment_method'],
                'plan_id' => $validated['plan_id'],
                'subscription_type' => $validated['subscription_type'],
                'amount' => $validated['amount'],
                'plan_type' => $validated['plan_type'],
                'caricature' => $validated['caricature'] ?? 0,
                'usage_type' => $validated['usage_type'] ?? $validated['plan_type'],
                'reference_id' => $referenceId,
                'status' => 'created',
            ]);

            // Create payment link
            $paymentLink = null;
            if ($validated['payment_method'] === 'razorpay') {
                $paymentLink = $this->createRazorpayPaymentLink($sale);

                if ($paymentLink) {
                    $sale->update([
                        'payment_link_id' => $paymentLink['id'],
                        'payment_link_url' => $paymentLink['payment_link_url'],
                        'short_url' => $paymentLink['short_url'],
                    ]);
                }
            } elseif ($validated['payment_method'] === 'phonepe') {
                $paymentLink = $this->createPhonePePaymentLink($sale);

                if ($paymentLink) {
                    $sale->update([
                        'payment_link_id' => $paymentLink['id'],
                        'phonepe_order_id' => $paymentLink['phonepe_order_id'] ?? null,
                        'payment_link_url' => $paymentLink['payment_link_url'],
                        'short_url' => $paymentLink['short_url'],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment link created successfully',
                'data' => [
                    'reference_id' => $sale->reference_id,
                    'payment_link' => $sale->short_url ?? $sale->payment_link_url,
                    'amount' => $sale->amount,
                    'payment_method' => $sale->payment_method,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Create Payment Link Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating payment link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/order-user/purchase-history/{userId}
     * Get purchase history for a user
     */
    public function getPurchaseHistory($userId): JsonResponse
    {
        try {
            $allOrders = Order::with(['subPlan', 'subscription', 'offerPackage.subPlan'])
                ->where('user_id', $userId)
                ->where('is_deleted', 0)
                ->whereIn('status', ['success', 'paid'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    $paymentMethod = 'Completed';

                    $sale = Sale::where('order_id', $order->id)->first();
                    if ($sale && $sale->payment_method) {
                        $paymentMethod = ucfirst($sale->payment_method);
                    } else {
                        if (!empty($order->razorpay_payment_id)) {
                            $paymentMethod = 'Razorpay';
                        } elseif (!empty($order->stripe_payment_intent_id) || !empty($order->stripe_txn_id)) {
                            $paymentMethod = 'Stripe';
                        }
                    }

                    $transactionId = $order->razorpay_payment_id
                        ?? $order->stripe_payment_intent_id
                        ?? $order->stripe_txn_id
                        ?? $order->crafty_id
                        ?? '-';

                    $planItemsDisplay = '-';

                    if (in_array($order->type, ['template', 'video', 'caricature'])) {
                        $planItems = $order->plan_items;
                        if ($planItems && $planItems->isNotEmpty()) {
                            $stringIds = $planItems->pluck('string_id')->filter()->toArray();
                            $planItemsDisplay = !empty($stringIds) ? implode(', ', $stringIds) : 'Custom Items';
                        } else {
                            $planItemsDisplay = 'Custom Items';
                        }
                    } elseif ($order->type === 'new_sub') {
                        $plan = $order->subPlan;
                        if ($plan) {
                            $planItemsDisplay = $plan->plan_name ?? 'New Subscription';
                        } else {
                            $planItemsDisplay = 'Subscription #' . $order->plan_id;
                        }
                    } elseif ($order->type === 'old_sub') {
                        $plan = $order->subscription;
                        if ($plan) {
                            $planItemsDisplay = $plan->package_name ?? 'Subscription';
                        } else {
                            $planItemsDisplay = 'Package #' . $order->plan_id;
                        }
                    } elseif ($order->type === 'offer') {
                        $offerPackage = $order->offerPackage;
                        if ($offerPackage && $offerPackage->subPlan) {
                            $planItemsDisplay = $offerPackage->subPlan->plan_name ?? 'Offer Plan';
                        } else {
                            $planItemsDisplay = 'Offer #' . $order->plan_id;
                        }
                    } else {
                        $planItemsDisplay = $order->plan_id ? $order->plan_id : 'Unknown';
                    }

                    return [
                        'id' => $order->id,
                        'amount' => $order->amount_with_symbol,
                        'status' => $order->status,
                        'type' => $order->type,
                        'plan_items' => $planItemsDisplay,
                        'payment_method' => $paymentMethod,
                        'transaction_id' => $transactionId,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'is_subscription_active' => $order->isSubscriptionActive(),
                        'user_name' => $order->user?->name ?? 'Unknown',
                        'email' => $order->user?->email ?? '',
                        'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '',
                    ];
                });

            $recentOrders = $allOrders->take(10);
            $remainingCount = $allOrders->count() - 10;

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $recentOrders,
                    'all_orders' => $allOrders,
                    'total_orders' => $allOrders->count(),
                    'showing_count' => $recentOrders->count(),
                    'remaining_count' => max(0, $remainingCount),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Purchase History Error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/order-user/check-phonepe-status/{merchantOrderId}
     * Check PhonePe payment status
     */
    public function checkPhonePeStatus($merchantOrderId): JsonResponse
    {
        try {
            $status = $this->checkPhonePePaymentStatus($merchantOrderId);

            if ($status) {
                return response()->json([
                    'success' => true,
                    'data' => $status
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status not found. Please check if the transaction ID is correct or if PhonePe credentials are configured properly.',
                    'merchant_order_id' => $merchantOrderId
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('PhonePe Status Check Error', [
                'merchant_order_id' => $merchantOrderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage(),
                'merchant_order_id' => $merchantOrderId
            ], 500);
        }
    }

    /**
     * GET /api/order-user/check-razorpay-status/{paymentLinkId}
     * Check Razorpay payment link status
     */
    public function checkRazorpayStatus($paymentLinkId): JsonResponse
    {
        try {
            $credentials = $this->getPaymentCredentials('razorpay', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('Razorpay credentials not configured');
            }

            $razorpayKey = $credentials['key_id'] ?? null;
            $razorpaySecret = $credentials['secret_key'] ?? $credentials['key_secret'] ?? null;

            if (!$razorpayKey || !$razorpaySecret) {
                throw new \Exception('Razorpay credentials not configured');
            }

            $url = 'https://api.razorpay.com/v1/payment_links/' . $paymentLinkId;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKey . ':' . $razorpaySecret);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch payment link status',
                    'http_code' => $httpCode
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/order-user/followup-labels
     * Get all available followup labels
     */
    public function getFollowupLabels(Request $request): JsonResponse
    {
        return $this->sendResponse($request, 200, true, "Followup labels fetched", [
            'data' => self::FOLLOWUP_LABELS
        ]);
    }

    /**
     * GET /api/order-user/new-orders
     * Get new orders since last ID (for real-time updates)
     */
    public function getNewOrders(Request $request): JsonResponse
    {
        try {
            $lastId = $request->get('last_id', 0);

            $newOrders = Order::with(['user'])
                ->where('is_deleted', 0)
                ->whereIn('status', ['failed', 'pending'])
                ->where('id', '>', $lastId)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($order) {
                    // Handle plan_items - it's a Collection of objects
                    $planItemsDisplay = '-';
                    try {
                        $planItems = $order->plan_items;
                        if ($planItems && $planItems->isNotEmpty()) {
                            // Extract string_id or id_name from each item
                            $itemNames = $planItems->map(function ($item) {
                                if (is_object($item)) {
                                    return $item->string_id ?? $item->id_name ?? $item->id ?? null;
                                }
                                return $item;
                            })->filter()->toArray();

                            $planItemsDisplay = !empty($itemNames) ? implode(', ', $itemNames) : $order->plan_id ?? '-';
                        } else {
                            $planItemsDisplay = $order->plan_id ?? '-';
                        }
                    } catch (\Exception $e) {
                        $planItemsDisplay = $order->plan_id ?? '-';
                    }

                    return [
                        'id' => $order->id,
                        'user_name' => $order->user?->name ?? '-',
                        'email' => $order->user?->email ?? '-',
                        'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '-',
                        'amount_with_symbol' => $order->amount_with_symbol ?? '-',
                        'type' => $order->type ?? '',
                        'plan_items' => $planItemsDisplay,
                        'status' => $order->status,
                        'is_subscription_active' => $order->isSubscriptionActive(),
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'email_template_count' => $order->email_template_count ?? 0,
                        'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                        'from_where' => $order->from_where ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $newOrders
            ]);

        } catch (\Exception $e) {
            Log::error('Get New Orders Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching new orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/order-user/create-order
     * Create a new order manually
     * Requires authentication
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'contact_no' => 'required|string|max:15',
                'plan_id' => 'required|string',
                'type' => 'required|in:old_sub,new_sub,template,video,caricature,offer',
                'amount' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'status' => 'nullable|in:pending,failed,success,paid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Get user by email
            $user = UserData::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this email. Please register first.'
                ], 404);
            }

            // Get authenticated user (sales person) from middleware
            $currentUser = $request->get('authenticated_user');

            // Generate crafty_id
            $craftyId = Order::generateCraftyId();

            // Generate unique razorpay_order_id (even though we're not using Razorpay)
            $razorpayOrderId = 'order_' . time() . rand(1000, 9999);

            // Create order
            $order = Order::create([
                'user_id' => $user->uid,
                'emp_id' => $currentUser ? $currentUser->id : 0,
                'plan_id' => $validated['plan_id'],
                'contact_no' => $validated['contact_no'],
                'crafty_id' => $craftyId,
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => '',
                'stripe_payment_intent_id' => '',
                'stripe_txn_id' => '',
                'status' => $validated['status'] ?? 'pending',
                'amount' => $validated['amount'],
                'paid' => 0,
                'currency' => $validated['currency'] ?? 'INR',
                'type' => $validated['type'],
                'is_deleted' => 0,
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
                'followup_call' => 0,
            ]);

            // Broadcast new order event
            try {
                WebSocketBroadcastController::broadcastOrderCreatedDirect($order);
            } catch (\Exception $e) {
                // Log but don't fail if broadcast fails
                Log::warning('Failed to broadcast order creation', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'id' => $order->id,
                    'crafty_id' => $order->crafty_id,
                    'user_id' => $order->user_id,
                    'user_name' => $user->name,
                    'email' => $user->email,
                    'contact_no' => $order->contact_no,
                    'plan_id' => $order->plan_id,
                    'type' => $order->type,
                    'amount' => $order->amount,
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Create Order Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Get payment credentials from payment_configurations table
     */
    private function getPaymentCredentials($gateway, $scope = 'NATIONAL')
    {
        $config = PaymentConfiguration::whereRaw('LOWER(gateway) = ?', [strtolower($gateway)])
            ->where('payment_scope', $scope)
            ->first();

        if (!$config) {
            Log::warning("Payment configuration not found", [
                'gateway' => $gateway,
                'scope' => $scope
            ]);
            return null;
        }

        return $config->credentials;
    }

    /**
     * Create Razorpay payment link
     */
    private function createRazorpayPaymentLink($sale)
    {
        try {
            $credentials = $this->getPaymentCredentials('razorpay', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('Razorpay credentials not configured in payment_configurations');
            }

            $razorpayKey = $credentials['key_id'] ?? null;
            $razorpaySecret = $credentials['secret_key'] ?? $credentials['key_secret'] ?? null;

            if (!$razorpayKey || !$razorpaySecret) {
                throw new \Exception('Razorpay credentials not configured');
            }

            $url = 'https://api.razorpay.com/v1/payment_links';

            $data = [
                'amount' => $sale->amount * 100,
                'currency' => 'INR',
                'accept_partial' => false,
                'description' => 'Payment for ' . $sale->plan_type . ' plan',
                'customer' => [
                    'name' => $sale->user_name,
                    'email' => $sale->email,
                    'contact' => $sale->contact_no,
                ],
                'notify' => [
                    'sms' => true,
                    'email' => true,
                ],
                'reminder_enable' => true,
                'reference_id' => $sale->reference_id,
                'callback_url' => url('/payment-link/callback'),
                'callback_method' => 'get',
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKey . ':' . $razorpaySecret);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return [
                    'id' => $result['id'],
                    'payment_link_url' => $result['short_url'] ?? $result['url'] ?? null,
                    'short_url' => $result['short_url'] ?? null,
                ];
            } else {
                $errorResponse = json_decode($response, true);
                $errorMessage = $errorResponse['error']['description'] ?? 'Razorpay API Error';

                Log::error('Razorpay API Error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error_message' => $errorMessage
                ]);

                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Razorpay Payment Link Creation Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create PhonePe payment link using OAuth
     */
    private function createPhonePePaymentLink($sale)
    {
        try {
            $credentials = $this->getPaymentCredentials('phonepe', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('PhonePe credentials not configured in payment_configurations');
            }

            $clientId = $credentials['client_id'] ?? null;
            $clientSecret = $credentials['client_secret'] ?? null;
            $merchantUserId = $credentials['merchant_user_id'] ?? $credentials['merchant_id'] ?? null;
            $environment = $credentials['environment'] ?? 'sandbox';

            if (!$clientId || !$clientSecret) {
                throw new \Exception('PhonePe client_id and client_secret are required');
            }

            // Get OAuth token
            $token = $this->getPhonePeOAuthToken($clientId, $clientSecret);

            $merchantOrderId = 'TX' . time() . rand(100000, 999999);
            $merchantSubscriptionId = 'MS' . time() . rand(100000, 999999);
            $baseUrl = url('/');
            $callbackUrl = $baseUrl . '/payment-link/phonepe-callback?ref=' . $sale->reference_id;

            // PhonePe OAuth API payload for one-time payment
            $payload = [
                'merchantOrderId' => $merchantOrderId,
                'amount' => (int) ($sale->amount * 100), // Amount in paise
                'expireAt' => now()->addMinutes(30)->timestamp * 1000,
                'metaInfo' => [
                    'udf1' => $sale->email,
                    'udf2' => $sale->name ?? 'Customer',
                    'udf3' => $sale->contact_no
                ],
                'paymentFlow' => [
                    'type' => 'SUBSCRIPTION_SETUP',
                    'merchantSubscriptionId' => $merchantSubscriptionId,
                    'authWorkflowType' => 'TRANSACTION',
                    'amountType' => 'FIXED',
                    'maxAmount' => (int) ($sale->amount * 100),
                    'recurringAmount' => (int) ($sale->amount * 100),
                    'frequency' => 'MONTHLY',
                    'expireAt' => now()->addYears(1)->timestamp * 1000,
                    'paymentMode' => [
                        'type' => 'UPI_COLLECT',
                        'details' => [
                            'type' => 'VPA',
                            'vpa' => 'default@ybl' // Default UPI, user can change
                        ]
                    ]
                ],
                'deviceContext' => [
                    'deviceOS' => 'ANDROID'
                ]
            ];

            // API URL based on environment
            $apiUrl = ($environment === 'production')
                ? 'https://api.phonepe.com/apis/pg/subscriptions/v2/setup'
                : 'https://api.phonepe.com/apis/pg/subscriptions/v2/setup';

            Log::info('PhonePe Payment Link Request (OAuth)', [
                'merchant_order_id' => $merchantOrderId,
                'amount' => $sale->amount,
                'api_url' => $apiUrl,
                'payload' => $payload
            ]);

            $response = \Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($apiUrl, $payload);

            $httpCode = $response->status();
            $responseData = $response->json();

            Log::info('PhonePe Payment Link Response (OAuth)', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            // Check for success response
            if ($httpCode === 200 && isset($responseData['orderId'])) {
                // For OAuth API, we need to construct the payment URL
                // The user will receive a UPI collect request
                return [
                    'id' => $merchantOrderId,
                    'phonepe_order_id' => $responseData['orderId'],
                    'phonepe_subscription_id' => $responseData['subscriptionId'] ?? null,
                    'payment_link_url' => $callbackUrl, // Redirect to callback for now
                    'short_url' => $callbackUrl,
                    'state' => $responseData['state'] ?? 'PENDING',
                    'message' => 'Payment request sent. User will receive UPI collect request.'
                ];
            } else {
                $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'PhonePe API error';
                $errorCode = $responseData['code'] ?? $responseData['errorCode'] ?? 'UNKNOWN';

                Log::error('PhonePe API Error (OAuth)', [
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'full_response' => $responseData
                ]);

                throw new \Exception("PhonePe Error [{$errorCode}]: {$errorMessage}");
            }

        } catch (\Exception $e) {
            Log::error('PhonePe Payment Link Creation Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get PhonePe OAuth access token
     */
    private function getPhonePeOAuthToken($clientId, $clientSecret)
    {
        // Check cache first
        $cachedToken = \Cache::get('phonepe_oauth_token');
        if ($cachedToken) {
            Log::info('Using cached PhonePe OAuth token');
            return $cachedToken;
        }

        // Generate new token
        $tokenUrl = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        Log::info('Generating new PhonePe OAuth token');

        $response = \Http::asForm()->post($tokenUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'client_version' => '1',
            'grant_type' => 'client_credentials',
        ]);

        $data = $response->json();

        if (!isset($data['access_token'])) {
            Log::error('PhonePe OAuth Token Generation Failed', [
                'response' => $data,
                'client_id' => $clientId
            ]);
            throw new \Exception('PhonePe OAuth failed: ' . json_encode($data));
        }

        $accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;

        // Cache token for 55 minutes (5 minutes before expiry)
        \Cache::put('phonepe_oauth_token', $accessToken, ($expiresIn - 300));

        Log::info('New PhonePe OAuth token generated', [
            'expires_in' => $expiresIn
        ]);

        return $accessToken;
    }

    /**
     * Check PhonePe payment status using OAuth
     */
    private function checkPhonePePaymentStatus($merchantTransactionId)
    {
        try {
            $credentials = $this->getPaymentCredentials('phonepe', 'NATIONAL');

            if (!$credentials) {
                Log::warning('PhonePe credentials not found', [
                    'merchant_transaction_id' => $merchantTransactionId
                ]);
                return null;
            }

            $merchantId = $credentials['merchant_id'] ?? null;
            $env = $credentials['environment'] ?? 'uat';

            if (!$merchantId) {
                Log::warning('PhonePe merchant_id not configured', [
                    'merchant_transaction_id' => $merchantTransactionId
                ]);
                return null;
            }

            $tokenService = app(PhonePeTokenService::class);
            $token = $tokenService->getAccessToken();

            if (!$token) {
                Log::warning('Failed to get PhonePe access token', [
                    'merchant_transaction_id' => $merchantTransactionId
                ]);
                return null;
            }

            $statusUrl = ($env === 'production')
                ? "https://api.phonepe.com/apis/pg/checkout/v2/order/{$merchantTransactionId}/status"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$merchantTransactionId}/status";

            Log::info('Checking PhonePe payment status', [
                'merchant_transaction_id' => $merchantTransactionId,
                'url' => $statusUrl,
                'environment' => $env
            ]);

            $response = \Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get($statusUrl);

            $httpCode = $response->status();
            $responseBody = $response->body();

            Log::info('PhonePe status check response', [
                'merchant_transaction_id' => $merchantTransactionId,
                'http_code' => $httpCode,
                'response' => $responseBody
            ]);

            // Parse response even if not successful to get error details
            $statusData = $response->json();

            // Check if order not found
            if ($httpCode === 400 && isset($statusData['code']) && $statusData['code'] === 'ORDER_NOT_FOUND') {
                Log::warning('PhonePe Order Not Found', [
                    'merchant_transaction_id' => $merchantTransactionId,
                    'message' => $statusData['message'] ?? 'Order not found'
                ]);

                return [
                    'success' => false,
                    'state' => 'NOT_FOUND',
                    'order_id' => $merchantTransactionId,
                    'message' => $statusData['message'] ?? 'Transaction not found',
                    'error_code' => $statusData['code'] ?? 'ORDER_NOT_FOUND',
                ];
            }

            if (!$response->successful()) {
                Log::warning('PhonePe Status Check Failed', [
                    'merchant_transaction_id' => $merchantTransactionId,
                    'http_code' => $httpCode,
                    'response' => $responseBody
                ]);
                return null;
            }

            $orderId = $statusData['orderId'] ?? null;
            $state = $statusData['state'] ?? 'UNKNOWN';
            $amount = $statusData['amount'] ?? null;
            $currency = $statusData['currency'] ?? 'INR';

            return [
                'success' => ($state === 'COMPLETED'),
                'state' => $state,
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_details' => $statusData['paymentDetails'] ?? [],
                'full_response' => $statusData,
            ];

        } catch (\Exception $e) {
            Log::error('PhonePe Status Check Error', [
                'merchant_transaction_id' => $merchantTransactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * POST /api/order-user/add-transaction-manually
     * Add transaction manually (requires authentication)
     */
    public function addTransactionManually(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'contact' => 'required|string|max:15',
                'method' => 'required|string',
                'transaction_id' => 'required|string',
                'currency_code' => 'required|string|in:INR,USD',
                'price_amount' => 'required|numeric|min:0',
                'paid_amount' => 'required|numeric|min:0',
                'plan_id' => 'required|integer',
                'usage_purpose' => 'required|in:personal,professional',
                'fromWallet' => 'nullable|boolean',
                'fromWhere' => 'nullable|string',
                'coins' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Find user by email
            $user = UserData::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this email'
                ], 404);
            }

            // Check if user is active
            if ($user->status == 0 && $user->status !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive'
                ], 400);
            }

            // Get plan details
            $plan = Subscription::find($validated['plan_id']);
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

            // Check if transaction already exists
            $existingTransaction = TransactionLog::where('transaction_id', $validated['transaction_id'])->first();
            if ($existingTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID already exists'
                ], 400);
            }

            // Create transaction log (same as panel)
            $transactionLog = new TransactionLog();
            $transactionLog->plan_id = $validated['plan_id'];
            $transactionLog->user_id = $user->uid; // Use uid, not id
            $transactionLog->payment_method = $validated['method'];
            $transactionLog->from_where = $validated['fromWhere'] ?? 'Manual';
            $transactionLog->transaction_id = $validated['transaction_id'];
            $transactionLog->promo_code_id = 0;

            // Set default values for required fields
            $transactionLog->cancellation_reason = '';
            $transactionLog->url = '';
            $transactionLog->subscription_status = '';
            $transactionLog->followup_note = '';
            $transactionLog->type = 0; // Old plan type
            $transactionLog->payment_status = 1; // Success
            $transactionLog->emp_id = 0; // API user
            $transactionLog->by_sales_team = 1;
            $transactionLog->subscription_is_active = 1;
            $transactionLog->is_trial = 0;
            $transactionLog->is_e_mandate = 0;
            $transactionLog->yearly = 0;
            $transactionLog->email_template_count = 0;
            $transactionLog->whatsapp_template_count = 0;
            $transactionLog->followup_call = 0;

            // Set currency and price (same as OrderUserController)
            $transactionLog->currency_code = $validated['currency_code'];
            if (!strcasecmp($validated['currency_code'], "INR")) {
                $transactionLog->price_amount = $plan->price;
            } else {
                $transactionLog->price_amount = $plan->price_dollar;
            }

            $transactionLog->paid_amount = $validated['paid_amount'];
            $transactionLog->net_amount = $validated['paid_amount'];
            $transactionLog->coins = $validated['coins'] ?? 0;
            $transactionLog->isManual = 1;

            // Deactivate old subscriptions
            TransactionLog::where('user_id', $user->uid)->update(['status' => 0]);

            // Set validity and expiry
            $transactionLog->validity = $plan->validity;
            $expiryDate = now()->addDays($plan->validity);
            $transactionLog->expired_at = $expiryDate;
            $transactionLog->save();

            // Update user premium status
            $user->is_premium = "1";
            $user->save();

            // Update personal details with usage purpose
            PersonalDetails::updateOrCreate(
                ['uid' => $user->uid], // Use uid
                ['usage' => $validated['usage_purpose']]
            );

            Log::info('Manual transaction added successfully via API', [
                'transaction_log_id' => $transactionLog->id,
                'user_id' => $user->id,
                'user_uid' => $user->uid,
                'email' => $validated['email'],
                'amount' => $validated['paid_amount'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully',
                'data' => [
                    'transaction_log_id' => $transactionLog->id,
                    'user_id' => $user->id,
                    'transaction_id' => $validated['transaction_id'],
                    'amount' => $validated['paid_amount'],
                    'currency' => $validated['currency_code'],
                    'plan_name' => $plan->package_name ?? 'Subscription',
                    'expiry_date' => $expiryDate->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Add Transaction Manually API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error adding transaction: ' . $e->getMessage()
            ], 500);
        }
    }
}
