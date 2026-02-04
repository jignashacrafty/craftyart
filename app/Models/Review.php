<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Review
 *
 * @property int $id
 * @property string|null $user_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $photo_uri
 * @property string $feedback
 * @property float $rate
 * @property int|null $is_approve
 * @property int $is_deleted
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\UserData|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereIsApprove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review wherePhotoUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUserId($value)
 * @mixin \Eloquent
 */
class Review extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'photo_uri',
        'feedback',
        'rate',
        'is_approve'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }
}
