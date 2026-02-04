<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PageSlugHistory
 *
 * @property int $id
 * @property int $emp_id
 * @property string $old_slug
 * @property string $new_slug
 * @property int $type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereNewSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereOldSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlugHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PageSlugHistory extends Model
{
	protected $table = 'page_slug_history';
	protected $connection = 'mysql';
    use HasFactory;
}
