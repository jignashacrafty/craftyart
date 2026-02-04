<?php

namespace App\Http\Controllers;

use App\Exports\CustomDataExport;
use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use App\Models\PurchaseHistory;
use App\Models\UserData;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomDataExporter extends AppBaseController
{

    function getDatas(): BinaryFileResponse
    {
        $purchases = PurchaseHistory::whereNotNull('contact_no')->orderBy('id', 'DESC')->get();

        $headings = ['Name', 'Email', 'Number', 'Currency', 'Amount'];
        $data = [];

        foreach ($purchases as $purchase) {
            $user = UserData::where('uid', $purchase->user_id)->first();
            if ($user) {
                $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->amount];
            }
        }

        return Excel::download(new CustomDataExport($data, $headings), 'dynamic-data.xlsx');
    }

    function getSubDatas(): BinaryFileResponse
    {
        // $purchases = TransactionLog::whereNotNull('contact_no')->orderBy('id', 'DESC')->get();

        $purchases = TransactionLog::whereNotNull('contact_no')
                    ->latest() // equivalent to orderBy('created_at', 'desc')
                    ->get()
                    ->unique('user_id')
                    ->values();

        $headings = ['Name', 'Email', 'Number', 'Currency', 'Amount', 'Status'];
        $data = [];

        foreach ($purchases as $purchase) {
            $user = UserData::where('uid', $purchase->user_id)->first();
            if ($user) {
                $status = $purchase->status === 1 ? "Active" : "InActive";
                if ($purchase->currency_code === 'Rs') {
                    $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->paid_amount, $status];
                } else {
                    $data[] = [$user->name, $user->email, $purchase->contact_no, $purchase->currency_code, $purchase->price_amount, $status];
                }
            }
        }

        return Excel::download(new CustomDataExport($data, $headings), 'dynamic-data.xlsx');
    }
}
