<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\PReview
 *
 * @property int $id
 * @property string|null $user_id
 * @property int $p_type
 * @property string $p_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $photo_uri
 * @property string $feedback
 * @property string|null $suggestion_type
 * @property string|null $summarised
 * @property float $rate
 * @property int|null $is_approve
 * @property int $is_deleted
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read mixed $page_type_name
 * @property-read \App\Models\UserData|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PReview query()
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereIsApprove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview wherePId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview wherePType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview wherePhotoUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereSuggestionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereSummarised($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PReview whereUserId($value)
 * @mixin \Eloquent
 */
class PReview extends Model
{
    protected $table = 'p_reviews';
    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'p_type',
        'p_id',
        'name',
        'email',
        'photo_uri',
        'suggestion_type',
        'summarised',
        'feedback',
        'rate',
        'is_approve',
        'is_deleted',
    ];
    public function getPageTypeNameAttribute()
    {
        $types = [
            0 => 'Product Page',
            1 => 'New Category',
            2 => 'Special Page',
            3 => 'Special Keyword',
            4 => 'Category',
            5 => 'Virtual Category',
        ];
        return $types[$this->p_type] ?? 'Unknown';
    }
    public const SUGGESTION_TYPES = [
        1 => 'Suggested',
        2 => 'Most Recent',
        3 => 'Highest Rating',
        4 => 'Lowest Rating',
    ];
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }
}