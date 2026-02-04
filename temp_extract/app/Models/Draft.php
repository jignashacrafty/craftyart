<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Draft
 *
 * @property int $id
 * @property string $string_id
 * @property string|null $template_id
 * @property string $user_id
 * @property string $name
 * @property string $ratio
 * @property int $width
 * @property int $height
 * @property string $thumbs
 * @property string $designs
 * @property int $is_premium
 * @property int $trashed
 * @property int $deleted
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Draft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Draft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Draft query()
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereDesigns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereThumbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereTrashed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Draft whereWidth($value)
 * @mixin \Eloquent
 */
class Draft extends Model
{
    protected $table = 'drafts';
    protected $connection = 'mysql';
    use HasFactory;
}
