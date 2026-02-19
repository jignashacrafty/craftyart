<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'display_name',
        'bio',
        'profile_image',
        'specializations',
        'commission_rate',
        'is_active',
        'total_designs',
        'approved_designs',
        'live_designs',
        'total_earnings',
    ];

    protected $casts = [
        'specializations' => 'array',
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(DesignerApplication::class, 'application_id');
    }

    public function wallet()
    {
        return $this->hasOne(DesignerWallet::class, 'designer_id');
    }

    public function designs()
    {
        return $this->hasMany(DesignSubmission::class, 'designer_id');
    }

    public function transactions()
    {
        return $this->hasMany(DesignerTransaction::class, 'designer_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(DesignerWithdrawal::class, 'designer_id');
    }
}
