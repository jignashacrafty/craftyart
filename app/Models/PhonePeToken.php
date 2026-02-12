<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhonePeToken extends Model
{
    protected $table = 'phonepe_tokens';
    
    protected $fillable = [
        'access_token',
        'token_type',
        'expires_in',
        'expires_at',
        'status',
        'metadata'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array'
    ];
    
    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
    
    /**
     * Check if token is expiring soon (within 5 minutes)
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at->diffInMinutes(now(), false) <= 5;
    }
    
    /**
     * Get active token
     */
    public static function getActiveToken()
    {
        return self::where('status', 'active')
                   ->where('expires_at', '>', now()->addMinutes(5))
                   ->orderBy('created_at', 'desc')
                   ->first();
    }
    
    /**
     * Mark token as expired
     */
    public function markAsExpired()
    {
        $this->status = 'expired';
        $this->save();
    }
}
