<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NewSearchTag
 *
 * @property int $id
 * @property string $name
 * @property string|null $id_name
 * @property string $new_category_id
 * @property int $total_templates
 * @property int $status
 * @property int|null $emp_id
 * @property int $assign_seo_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User|null $assignedSeo
 * @property-read \App\Models\NewCategory|null $newCategories
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereAssignSeoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereNewCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereTotalTemplates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewSearchTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NewSearchTag extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'name',
        'id_name',
        'new_category_id',
        'seo_emp_id',
        'status',
        'emp_id',
    ];
    public function newCategories()
    {
        return $this->belongsTo(NewCategory::class, 'new_category_id', 'id');
    }

    public function assignedSeo()
    {
        return $this->belongsTo(User::class, 'seo_emp_id');
    }

}
