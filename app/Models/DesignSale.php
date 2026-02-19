<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignSale extends Model
{
    protected $fillable = [
        'design_submission_id',
        'designer_id',
        'purchase_history_id',
        'user_id',
        'sale_amount',
        'designer_commission',
        'commission_rate',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'designer_commission' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function design()
    {
        return $this->belongsTo(DesignSubmission::class, 'design_submission_id');
    }

    public function designer()
    {
        return $this->belongsTo(DesignerProfile::class, 'designer_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
