<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    /**
     * Get new orders since last_id
     */
    public function getNewOrders(Request $request)
    {
        try {
            $lastId = $request->input('last_id', 0);
            
            // Get new orders since last_id
            $orders = Order::where('id', '>', $lastId)
                ->where('is_deleted', 0)
                ->whereIn('status', ['pending', 'failed'])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();
            
            $result = [];
            foreach ($orders as $order) {
                $result[] = [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->amount,
                    'status' => $order->status,
                    'type' => $order->type,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            }
            
            return response()->json([
                'success' => true,
                'orders' => $result,
                'count' => count($result)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
