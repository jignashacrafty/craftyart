<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ReportTable
 *
 * @property int $id
 * @property string $user_id
 * @property int|null $asset_id
 * @property int $type
 * @property string $title
 * @property string|null $sub_title
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportTable whereUserId($value)
 * @mixin \Eloquent
 */
class ReportTable extends Model
{
	protected $table = 'reports';
	protected $connection = 'mysql';
    use HasFactory;
}
