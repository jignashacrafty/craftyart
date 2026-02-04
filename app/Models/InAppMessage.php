<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\InAppMessage
 *
 * @property int $id
 * @property string $image
 * @property int|null $open_type
 * @property int|null $can_cancle
 * @property int|null $is_banner
 * @property string|null $keyword
 * @property string|null $link
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereCanCancle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereIsBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereOpenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppMessage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InAppMessage extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
