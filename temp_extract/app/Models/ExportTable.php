<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExportTable
 *
 * @property int $id
 * @property string|null $uid
 * @property string $original_name
 * @property string $name
 * @property string $path
 * @property int $total
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExportTable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExportTable extends Model
{
	protected $table = 'exports';
	protected $connection = 'mysql';
    use HasFactory;
}
