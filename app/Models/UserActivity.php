<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserActivity
 *
 * @property int $id
 * @property string $uid
 * @property string $last_login_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereUid($value)
 * @mixin \Eloquent
 */
class UserActivity extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
