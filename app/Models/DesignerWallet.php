<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignerWallet extends Model
{
    protected $fillable = [
        'designer_id',
        'wallet_setting_id',
        'balance',
        'total_earned',
        'total_sold_amount',
        'platform_commission',
        'total_withdrawn',
        'pending_amount',
        'withdrawal_threshold',
        'total_sales_count',
        'average_sale_amount',
        'last_sale_at',
        'last_withdrawal_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_sold_amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'withdrawal_threshold' => 'decimal:2',
        'average_sale_amount' => 'decimal:2',
        'last_sale_at' => 'datetime',
        'last_withdrawal_at' => 'datetime',
    ];

    public function designer()
    {
        return $this->belongsTo(DesignerProfile::class, 'designer_id');
    }

    public function walletSetting()
    {
        return $this->belongsTo(WalletSetting::class, 'wallet_setting_id');
    }

    public function canWithdraw()
    {
        return $this->balance >= $this->withdrawal_threshold;
    }

    public function addSale($saleAmount, $commissionRate = null)
    {
        if ($commissionRate === null) {
            $setting = $this->walletSetting ?? WalletSetting::getDefault();
            $commissionRate = $setting ? $setting->platform_commission_rate : 30;
        }

        $commission = ($saleAmount * $commissionRate) / 100;
        $designerEarning = $saleAmount - $commission;

        $this->total_sold_amount += $saleAmount;
        $this->platform_commission += $commission;
        $this->total_earned += $designerEarning;
        $this->balance += $designerEarning;
        $this->total_sales_count += 1;
        $this->average_sale_amount = $this->total_sold_amount / $this->total_sales_count;
        $this->last_sale_at = now();
        $this->save();

        return $designerEarning;
    }
}
