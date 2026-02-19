<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignerTransaction extends Model
{
    protected $fillable = [
        'designer_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'transaction_type',
        'description',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function designer()
    {
        return $this->belongsTo(DesignerProfile::class, 'designer_id');
    }
}
