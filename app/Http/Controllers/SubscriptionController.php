<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\PaymentSetting;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\Design;
use App\Models\Video\VideoPurchaseHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends AppBaseController
{

    public function index()
    {
    }

    public function show_package(Subscription $subscription)
    {
        return view('subscription/show_package')->with('packageArray', Subscription::all());
    }

    public function showPaymentSetting(PaymentSetting $paymentSetting)
    {
        $payment = PaymentSetting::find(1);
        $payment = ($payment == null) ? [] : $payment;
        return view('subscription/payment_setting')->with('payment', $payment);
    }

    public function showTranscation(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        if ($request->has('query')) {
            $temp_data_count = TransactionLog::where('plan_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('paid_amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = TransactionLog::where('plan_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('paid_amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = TransactionLog::orderBy('id', 'desc')->count();
            $temp_data = TransactionLog::orderBy('id', 'desc')->paginate(10);
        }


        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        $data['packageArray'] = Subscription::all();

        return view('subscription/show_transcation')->with('datas', $data);
    }

    public function showPurchases(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        if ($request->has('query')) {
            $temp_data_count = PurchaseHistory::where('product_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = PurchaseHistory::where('product_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('user_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('transaction_id', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('amount', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('payment_method', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('from_where', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = PurchaseHistory::orderBy('id', 'desc')->count();
            $temp_data = PurchaseHistory::orderBy('id', 'desc')->paginate(10);
        }

        $temp_data->getCollection()->transform(function ($purchase) {
            $res = Design::where('string_id', $purchase->product_id)->first();
            $purchase->thumb = $res->post_thumb;
            return $purchase;
        });

        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationArray'] = $temp_data;
        return view('subscription/show_purchases')->with('datas', $data);
    }

    public function addPackage(Request $request)
    {
        $res = new Subscription;
        $res->package_name = $request->input('package_name');
        $res->desc = $request->input('desc');
        $res->validity = $request->input('validity');
        $res->actual_price = $request->input('actual_price');
        $res->price = $request->input('price');
        $res->actual_price_dollar = $request->input('actual_price_dollar');
        $res->price_dollar = $request->input('price_dollar');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Added successfully.'
        ]);
    }

    public function updatePackage(Request $request, Subscription $subscription)
    {

        $res = Subscription::find($request->id);
        $res->package_name = $request->input('package_name');
        $res->desc = $request->input('desc');
        $res->validity = $request->input('validity');
        $res->actual_price = $request->input('actual_price');
        $res->price = $request->input('price');
        $res->actual_price_dollar = $request->input('actual_price_dollar');
        $res->price_dollar = $request->input('price_dollar');
        $res->status = $request->input('status');
        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function deletePackage(Request $request, Subscription $subscription)
    {
        // Subscription::destroy(array('id', $request->id));
        return response()->json([
            'success' => $request->id
        ]);
    }

    public function updatePaymentSetting(Request $request, PaymentSetting $paymentSetting)
    {

        $res = PaymentSetting::find($request->id);
        $res->razorpay_status = $request->input('razorpay_status');
        $res->stripe_status = $request->input('stripe_status');
        $res->paypal_status = $request->input('paypal_status');

        $res->razorpay_ki = $request->input('razorpay_ki');
        $res->razorpay_ck = $request->input('razorpay_ck');

        $res->stripe_sk = $request->input('stripe_sk');
        $res->stripe_pk = $request->input('stripe_pk');
        $res->stripe_ver = $request->input('stripe_ver');

        $res->paypal_ci = $request->input('paypal_ci');
        $res->paypal_sk = $request->input('paypal_sk');

        $res->save();
        return response()->json([
            'success' => 'Data Updated successfully.'
        ]);
    }

    public function showTemplateTranscation(Request $request)
    {

        $temp_data = [];
        $temp_data_count = 0;
        if ($request->has('query')) {
            $query = $request->input('query');
            $temp_data_count = PurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = PurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = PurchaseHistory::with('userData')->orderBy('id', 'desc')->count();
            $temp_data = PurchaseHistory::with('userData')->orderBy('id', 'desc')->paginate(10);
        }

        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationTemplateArray'] = $temp_data;

        return view('subscription/show_template_transcation')->with('datas', $data);
    }

    public function showVideoTranscation(Request $request)
    {
        $temp_data = [];
        $temp_data_count = 0;

        if ($request->has('query')) {
            $query = $request->input('query');
            $temp_data_count = VideoPurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->count();

            $temp_data = VideoPurchaseHistory::where(function ($q) use ($query) {
                $q->where('user_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $query . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $query . '%')
                    ->orWhere('from_where', 'LIKE', '%' . $query . '%');
            })
                ->orderBy('id', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = VideoPurchaseHistory::with('userData')->orderBy('id', 'desc')->count();
            $temp_data = VideoPurchaseHistory::with('userData')->orderBy('id', 'desc')->paginate(10);
        }


        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['transcationVideArray'] = $temp_data;

        return view('subscription/show_video_transcation')->with('datas', $data);
    }
}
