<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserSession
 *
 * @property int $id
 * @property int $user_id
 * @property string $device_id
 * @property int|null $token_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $last_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UserData|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereLastActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSession whereUserId($value)
 * @mixin \Eloquent
 */
class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'device_id',
        'token_id',
        'custom_token',
        'ip_address',
        'user_agent',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(UserData::class, 'user_id');
    }

}
