<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ContactUsWeb
 *
 * @property int $id
 * @property string|null $user_id
 * @property string $name
 * @property string $email
 * @property string $message
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $system_info
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereSystemInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUsWeb whereUserId($value)
 * @mixin \Eloquent
 */
class ContactUsWeb extends Model
{
    use HasFactory;

    protected $table = 'contact_us_web';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'message',
        'ip_address',
        'user_agent',
        'system_info'
    ];
}