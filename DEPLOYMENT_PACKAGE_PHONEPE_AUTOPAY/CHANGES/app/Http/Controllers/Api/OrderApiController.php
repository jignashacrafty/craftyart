<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\RoleManager;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    /**
     * Get new orders and check status changes in a single API call
     * Combined API for better performance
     */
    public function syncOrders(Request $request)
    {
        try {
            $lastId = $request->input('last_id', 0);
            $orderIds = $request->input('order_ids', []);
            
            // 1. Get new orders since last_id
            $query = Order::with(['user'])
                ->where('id', '>', $lastId)
                ->where('is_deleted', 0)
                ->whereIn('status', ['pending', 'failed']);
            
            // Apply sales employee filter
            $isSalesEmployee = RoleManager::isSalesEmployee(auth()->user()->user_type);
            
            if ($isSalesEmployee) {
                $userId = auth()->user()->id;
                $query->where(function($q) use ($userId) {
                    $q->whereNull('emp_id')
                      ->orWhere('emp_id', 0)
                      ->orWhere('emp_id', $userId);
                });
            }
            
            $newOrders = $query->orderBy('id', 'desc')
                ->limit(10)
                ->get();
            
            // 2. Check for status changes in existing orders
            $removedOrders = [];
            if (!empty($orderIds)) {
                $removedOrders = Order::whereIn('id', $orderIds)
                    ->where(function($q) {
                        $q->whereNotIn('status', ['pending', 'failed'])
                          ->orWhere('is_deleted', 1);
                    })
                    ->pluck('id')
                    ->toArray();
            }
            
            // 3. Format new orders data
            $result = [];
            foreach ($newOrders as $order) {
                $planItems = $order->plan_items;
                
                // Format plan items for display
                $planItemsDisplay = '-';
                if ($planItems && $planItems->isNotEmpty()) {
                    $firstItem = $planItems->first();
                    if (is_object($firstItem) && isset($firstItem->string_id)) {
                        $stringIds = $planItems->pluck('string_id')->filter()->toArray();
                        $planItemsDisplay = implode(', ', $stringIds);
                    } else {
                        $planItemsDisplay = $firstItem ?? '-';
                    }
                }
                
                // Get employee name for Follow By column
                $followByName = '-';
                if (!empty($order->emp_id)) {
                    $employee = \App\Models\User::find($order->emp_id);
                    $followByName = $employee ? $employee->name : 'N/A';
                }
                
                $result[] = [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'user_name' => $order->user?->name ?? '-',
                    'email' => $order->user?->email ?? '-',
                    'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '-',
                    'amount' => $order->amount,
                    'amount_with_symbol' => $order->amount_with_symbol ?? '-',
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'type' => $order->type,
                    'plan_items' => $planItemsDisplay,
                    'is_subscription_active' => $order->isSubscriptionActive(),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'email_template_count' => $order->email_template_count ?? 0,
                    'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                    'from_where' => $order->from_where ?? '-',
                    'followup_call' => $order->followup_call ?? 0,
                    'follow_by' => $followByName,
                    'emp_id' => $order->emp_id ?? 0,
                ];
            }
            
            return response()->json([
                'success' => true,
                'new_orders' => $result,
                'removed_orders' => $removedOrders,
                'new_count' => count($result),
                'removed_count' => count($removedOrders)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
