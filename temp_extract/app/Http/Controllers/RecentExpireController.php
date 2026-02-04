<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\TransactionLog;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecentExpireController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        $activeUserIds = TransactionLog::where('expired_at', '>', now())
            ->pluck('user_id')
            ->toArray();

        $query = TransactionLog::with(['userData', 'subscription', 'subPlan', 'offer'])
            ->where('expired_at', '<', now())
            ->whereNotIn('user_id', $activeUserIds)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('transaction_logs')
                    ->groupBy('user_id');
            });

        $filterType = $request->get('filter_type', 'all');
        $amountSort = $request->get('amount_sort');
        $whatsappFilter = $request->get('whatsapp_filter');
        $emailFilter = $request->get('email_filter');
        $followupFilter = $request->get('followup_filter');

        if ($whatsappFilter === 'sent') {
            $query->where('transaction_logs.whatsapp_template_count', '>', 0);
        } elseif ($whatsappFilter === 'not_sent') {
            $query->where('transaction_logs.whatsapp_template_count', '<=', 0);
        }

        if ($emailFilter === 'sent') {
            $query->where('transaction_logs.email_template_count', '>', 0);
        } elseif ($emailFilter === 'not_sent') {
            $query->where('transaction_logs.email_template_count', '<=', 0);
        }

        if ($followupFilter === 'called') {
            $query->where('transaction_logs.followup_call', 1);
        } elseif ($followupFilter === 'not_called') {
            $query->where('transaction_logs.followup_call', 0);
        }

        // Step 4: sorting
        if (in_array($amountSort, ['asc', 'desc'])) {
            $request->merge(['sort_by' => 'transaction_logs.paid_amount']);
            $request->merge(['sort_order' => $amountSort]);
        } else {
            $request->merge(['sort_by' => 'transaction_logs.expired_at']);
            $request->merge(['sort_order' => 'desc']);
        }

        // Step 5: pagination (your existing helper)
        $recentExpires = $this->applyFiltersAndPagination($request, $query, []);

        // Step 6: load packages
        $datas['packageArray'] = Subscription::all();

        // Step 7: return view
        return view('recent_expire.index', compact(
            'recentExpires',
            'filterType',
            'amountSort',
            'whatsappFilter',
            'emailFilter',
            'followupFilter',
            'datas'
        ));
    }

    public function followupUpdate(Request $request): JsonResponse
    {
        $orderUser = TransactionLog::findOrFail($request->id);

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
}