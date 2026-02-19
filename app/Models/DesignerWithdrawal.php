<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignerWithdrawal extends Model
{
    protected $fillable = [
        'designer_id',
        'amount',
        'status',
        'bank_name',
        'account_number',
        'ifsc_code',
        'account_holder_name',
        'upi_id',
        'payment_method',
        'transaction_reference',
        'admin_notes',
        'rejection_reason',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function designer()
    {
        return $this->belongsTo(DesignerProfile::class, 'designer_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
