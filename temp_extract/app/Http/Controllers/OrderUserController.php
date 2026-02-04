<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderUserController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $searchTerm = $request->get('search');

        $query = Order::query()
            ->where('is_deleted', 0)
            ->whereIn('orders.status', ['failed', 'pending'])
            ->where(function ($q) {
                $q->where('orders.type', 'caricature')
                    ->orWhereNotIn('orders.user_id', function ($subQuery) {
                        $subQuery->select('user_id')
                            ->from('transaction_logs')
                            ->where('expired_at', '>', now());
                    });
            });

        $filterType = $request->get('filter_type', 'all');
        $statusFilter = $request->get('status_filter', 'all');
        $typeFilter = $request->get('type_filter');
        $amountSort = $request->get('amount_sort');
        $whatsappFilter = $request->get('whatsapp_filter');
        $emailFilter = $request->get('email_filter');
        $followupFilter = $request->get('followup_filter');

        if ($filterType === 'remove_duplicate') {
            $query->whereIn('orders.id', function ($subQuery) {
                $subQuery->select(DB::raw('MAX(id)'))
                    ->from('orders')
                    ->whereIn('status', ['failed', 'pending'])
                    ->groupBy('user_id', 'plan_id');
            });
        }

        if (in_array($statusFilter, ['pending', 'failed'])) {
            $query->where('orders.status', $statusFilter);
        }

        if (in_array($typeFilter, ['old_sub', 'new_sub', 'template', 'video'])) {
            $query->where('orders.type', $typeFilter);
        }

        if ($whatsappFilter === 'sent') {
            $query->where('orders.whatsapp_template_count', '>', 0);
        } elseif ($whatsappFilter === 'not_sent') {
            $query->where('orders.whatsapp_template_count', '<=', 0);
        }

        if ($emailFilter === 'sent') {
            $query->where('orders.email_template_count', '>', 0);
        } elseif ($emailFilter === 'not_sent') {
            $query->where('orders.email_template_count', '<=', 0);
        }

        if ($followupFilter === 'called') {
            $query->where('orders.followup_call', 1);
        } elseif ($followupFilter === 'not_called') {
            $query->where('orders.followup_call', 0);
        }

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('orders.id', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.amount', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.type', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.plan_id', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.status', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.currency', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.contact_no', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('orders.followup_note', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('number', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        if (in_array($amountSort, ['asc', 'desc'])) {
            $request->merge(['sort_by' => 'orders.amount']);
            $request->merge(['sort_order' => $amountSort]);
        } else {
            $request->merge(['sort_by' => 'orders.id']);
            $request->merge(['sort_order' => 'desc']);
        }

        $OrderUsers = $this->applyFiltersAndPagination($request, $query, $searchableFields = []);

        $allPlanIds = [];
        foreach ($OrderUsers as $order) {
            if (in_array($order->type, ['template', 'video']) && !empty($order->plan_id)) {
                $planData = json_decode($order->plan_id, true);
                $ids = collect($planData)->pluck('id')->toArray();
                $allPlanIds = array_merge($allPlanIds, $ids);
            }
        }
        $allPlanIds = array_unique($allPlanIds);

        $allDesigns = Design::whereIn('string_id', $allPlanIds)
            ->get()->keyBy('string_id');

        $datas['packageArray'] = Subscription::all();

        return view('order_user.index', compact(
            'OrderUsers',
            'searchableFields',
            'filterType',
            'statusFilter',
            'typeFilter',
            'amountSort',
            'whatsappFilter',
            'emailFilter',
            'followupFilter',
            'allDesigns',
            'datas',
            'searchTerm'
        ));
    }



    public function followupUpdate(Request $request): JsonResponse
    {
        $orderUser = Order::findOrFail($request->id);

        if ($request->has('followup_call') && $request->followup_call == 0) {
            $orderUser->followup_call = 0;
            $orderUser->followup_note = null;
        } else {
            $orderUser->followup_call = 1;
            $orderUser->followup_note = $request->followup_note ?? '';
        }

        $orderUser->save();

        return response()->json([
            'success' => true,
            'message' => 'Followup updated successfully'
        ]);
    }

    public function apiAddAndCheckDuplicates(Request $request)
    {
        $order = Order::create($request->all());
        if ($order->status === 'success') {
            $updatedCount = Order::where('user_id', $order->user_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', ['pending', 'failed'])
                ->where('is_deleted', 0)
                ->update(['is_deleted' => 1]);
        }
        return response()->json([
            'success' => true,
            'order_created' => $order,
            'duplicates_updated' => $updatedCount
        ]);
    }
}