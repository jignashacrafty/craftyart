<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OtpTable
 *
 * @property int $id
 * @property string|null $mail
 * @property string|null $otp
 * @property string|null $msg
 * @property int|null $type
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable query()
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereOtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtpTable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OtpTable extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
}