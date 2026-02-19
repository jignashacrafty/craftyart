<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletSettingController extends Controller
{
    /**
     * Display wallet settings page
     */
    public function index()
    {
        $settings = WalletSetting::orderBy('created_at', 'desc')->get();
        return view('admin.wallet_settings.index', compact('settings'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.wallet_settings.create');
    }

    /**
     * Store new wallet setting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setting_key' => 'required|string|unique:wallet_settings,setting_key',
            'setting_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_withdrawal_threshold' => 'required|numeric|min:0',
            'max_withdrawal_limit' => 'nullable|numeric|min:0',
            'payout_day_of_month' => 'required|integer|min:1|max:31',
            'payout_frequency' => 'required|in:daily,weekly,monthly',
            'platform_commission_rate' => 'required|numeric|min:0|max:100',
            'min_days_between_withdrawals' => 'required|integer|min:0',
            'max_pending_withdrawals' => 'required|integer|min:1',
            'auto_approve_withdrawals' => 'boolean',
            'auto_approve_threshold' => 'nullable|numeric|min:0',
            'payment_methods' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        WalletSetting::create($request->all());

        return redirect()->route('admin.wallet-settings.index')
            ->with('success', 'Wallet setting created successfully');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $setting = WalletSetting::findOrFail($id);
        return view('admin.wallet_settings.edit', compact('setting'));
    }

    /**
     * Update wallet setting
     */
    public function update(Request $request, $id)
    {
        $setting = WalletSetting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'setting_name' => 'required|string|max:255',
            'min_withdrawal_threshold' => 'required|numeric|min:0',
            'max_withdrawal_limit' => 'nullable|numeric|min:0',
            'payout_day_of_month' => 'required|integer|min:1|max:31',
            'payout_frequency' => 'required|in:daily,weekly,monthly',
            'platform_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update only the fields from the form
        $setting->update([
            'setting_name' => $request->setting_name,
            'min_withdrawal_threshold' => $request->min_withdrawal_threshold,
            'max_withdrawal_limit' => $request->max_withdrawal_limit,
            'payout_day_of_month' => $request->payout_day_of_month,
            'payout_frequency' => $request->payout_frequency,
            'platform_commission_rate' => $request->platform_commission_rate,
        ]);

        return redirect()->route('designer_system.wallet_settings')
            ->with('success', 'Wallet setting updated successfully');
    }

    /**
     * Delete wallet setting
     */
    public function destroy($id)
    {
        $setting = WalletSetting::findOrFail($id);
        
        if ($setting->setting_key === 'default') {
            return redirect()->back()
                ->with('error', 'Cannot delete default wallet setting');
        }

        $setting->delete();

        return redirect()->route('admin.wallet-settings.index')
            ->with('success', 'Wallet setting deleted successfully');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $setting = WalletSetting::findOrFail($id);
        $setting->is_active = !$setting->is_active;
        $setting->save();

        return redirect()->back()
            ->with('success', 'Wallet setting status updated');
    }
}
