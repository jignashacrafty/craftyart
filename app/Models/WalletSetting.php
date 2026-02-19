<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_name',
        'description',
        'min_withdrawal_threshold',
        'max_withdrawal_limit',
        'payout_day_of_month',
        'payout_frequency',
        'platform_commission_rate',
        'min_days_between_withdrawals',
        'max_pending_withdrawals',
        'auto_approve_withdrawals',
        'auto_approve_threshold',
        'payment_methods',
        'additional_settings',
        'is_active',
    ];

    protected $casts = [
        'min_withdrawal_threshold' => 'decimal:2',
        'max_withdrawal_limit' => 'decimal:2',
        'platform_commission_rate' => 'decimal:2',
        'auto_approve_threshold' => 'decimal:2',
        'payment_methods' => 'array',
        'additional_settings' => 'array',
        'auto_approve_withdrawals' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function wallets()
    {
        return $this->hasMany(DesignerWallet::class, 'wallet_setting_id');
    }

    public static function getDefault()
    {
        return self::where('setting_key', 'default')->where('is_active', true)->first();
    }
}
