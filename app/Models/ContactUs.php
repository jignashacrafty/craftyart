<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ContactUs
 *
 * @property int $id
 * @property string $user_id
 * @property string $brand
 * @property string $message
 * @property int $width
 * @property int $height
 * @property int $is_file
 * @property int $from_user
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User|null $userData
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereFromUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereIsFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactUs whereWidth($value)
 * @mixin \Eloquent
 */
class ContactUs extends Model
{
	protected $connection = 'mysql';
    use HasFactory;


    public function userData()
    {
        return $this->belongsTo(User::class);
    }
}
