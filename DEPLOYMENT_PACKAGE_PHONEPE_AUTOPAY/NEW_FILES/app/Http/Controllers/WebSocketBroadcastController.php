<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebSocketBroadcastController extends Controller
{
    /**
     * Broadcast order event directly using HTTP API (bypassing Pusher library)
     */
    public static function broadcastOrderCreatedDirect($order)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');
            
            $channel = 'orders';
            $event = 'new-order-created';
            
            // Get employee name for Follow By column
            $followByName = '-';
            if (!empty($order->emp_id)) {
                $employee = \App\Models\User::find($order->emp_id);
                $followByName = $employee ? $employee->name : 'N/A';
            }
            
            // Format plan items for display
            $planItems = $order->plan_items;
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
            
            // Prepare order data
            $orderData = [
                'order' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id ?? 'unknown',
                    'user_name' => $order->user?->name ?? '-',
                    'email' => $order->user?->email ?? '-',
                    'contact_no' => $order->contact_no ?? $order->user?->contact_no ?? '-',
                    'amount' => $order->amount ?? '0',
                    'amount_with_symbol' => $order->amount_with_symbol ?? '₹' . ($order->amount ?? '0'),
                    'status' => $order->status ?? 'pending',
                    'type' => $order->type ?? 'old_sub',
                    'created_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'plan_items' => $planItemsDisplay,
                    'is_subscription_active' => $order->isSubscriptionActive(),
                    'email_template_count' => $order->email_template_count ?? 0,
                    'whatsapp_template_count' => $order->whatsapp_template_count ?? 0,
                    'from_where' => $order->from_where ?? '-',
                    'followup_call' => $order->followup_call ?? 0,
                    'follow_by' => $followByName,
                    'emp_id' => $order->emp_id ?? 0,
                ]
            ];
            
            $data = json_encode($orderData);
            
            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);
            
            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);
            
            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);
            
            $authSignature = hash_hmac('sha256', $stringToSign, $secret);
            
            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";
            
            \Log::info('WebSocketBroadcast: Sending direct HTTP API request', [
                'url' => $url,
                'body_size' => strlen($body)
            ]);
            
            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Direct HTTP API success', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Direct HTTP API failed', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error' => $error
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Direct HTTP API exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return false;
        }
    }

    /**
     * Broadcast order status change (for removal from pending/failed list)
     */
    public static function broadcastOrderStatusChanged($order, $oldStatus, $newStatus)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');
            
            $channel = 'orders';
            $event = 'order-status-changed';
            
            // Prepare status change data
            $statusData = [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'should_remove' => !in_array($newStatus, ['pending', 'failed']),
            ];
            
            $data = json_encode($statusData);
            
            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);
            
            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);
            
            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);
            
            $authSignature = hash_hmac('sha256', $stringToSign, $secret);
            
            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";
            
            \Log::info('WebSocketBroadcast: Sending status change event', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Status change event sent successfully', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Status change event failed', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Status change exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return false;
        }
    }

    /**
     * Broadcast order followup change (for real-time checkbox and follow_by updates)
     */
    public static function broadcastOrderFollowUpChanged($order)
    {
        try {
            $appId = config('broadcasting.connections.pusher.app_id');
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
            $host = config('broadcasting.connections.pusher.options.host');
            $port = config('broadcasting.connections.pusher.options.port');
            
            $channel = 'orders';
            $event = 'order-followup-changed';
            
            // Get employee name for Follow By column
            $followByName = '-';
            if (!empty($order->emp_id)) {
                $employee = \App\Models\User::find($order->emp_id);
                $followByName = $employee ? $employee->name : 'N/A';
            }
            
            // Get followup label display text
            $followupLabelDisplay = '';
            if (!empty($order->followup_label)) {
                $labels = \App\Http\Controllers\OrderUserController::FOLLOWUP_LABELS;
                $followupLabelDisplay = $labels[$order->followup_label] ?? $order->followup_label;
            }
            
            // Prepare followup change data
            $followupData = [
                'order_id' => $order->id,
                'followup_call' => $order->followup_call ?? 0,
                'followup_note' => $order->followup_note ?? '',
                'followup_label' => $order->followup_label ?? '',
                'followup_label_display' => $followupLabelDisplay,
                'follow_by' => $followByName,
                'emp_id' => $order->emp_id ?? 0,
            ];
            
            $data = json_encode($followupData);
            
            // Prepare the request body
            $body = json_encode([
                'name' => $event,
                'channels' => [$channel],
                'data' => $data
            ]);
            
            // Generate Pusher authentication signature
            $timestamp = time();
            $method = 'POST';
            $path = "/apps/{$appId}/events";
            $bodyMd5 = md5($body);
            
            $stringToSign = implode("\n", [
                $method,
                $path,
                "auth_key={$key}&auth_timestamp={$timestamp}&auth_version=1.0&body_md5={$bodyMd5}"
            ]);
            
            $authSignature = hash_hmac('sha256', $stringToSign, $secret);
            
            // Build the URL with query parameters
            $url = "http://{$host}:{$port}{$path}";
            $url .= "?auth_key={$key}";
            $url .= "&auth_timestamp={$timestamp}";
            $url .= "&auth_version=1.0";
            $url .= "&body_md5={$bodyMd5}";
            $url .= "&auth_signature={$authSignature}";
            
            \Log::info('WebSocketBroadcast: Sending followup change event', [
                'order_id' => $order->id,
                'followup_call' => $order->followup_call,
                'follow_by' => $followByName
            ]);
            
            // Make the HTTP request using cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200) {
                \Log::info('WebSocketBroadcast: Followup change event sent successfully', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode
                ]);
                return true;
            } else {
                \Log::error('WebSocketBroadcast: Followup change event failed', [
                    'order_id' => $order->id,
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            \Log::error('WebSocketBroadcast: Followup change exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return false;
        }
    }
}
