<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SearchTag
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $assign_seo_id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User|null $assignedSeo
 * @method static Builder|SearchTag newModelQuery()
 * @method static Builder|SearchTag newQuery()
 * @method static Builder|SearchTag query()
 * @method static Builder|SearchTag whereAssignSeoId($value)
 * @method static Builder|SearchTag whereCreatedAt($value)
 * @method static Builder|SearchTag whereEmpId($value)
 * @method static Builder|SearchTag whereId($value)
 * @method static Builder|SearchTag whereName($value)
 * @method static Builder|SearchTag whereStatus($value)
 * @method static Builder|SearchTag whereUpdatedAt($value)
 * @property int $seo_emp_id
 * @method static Builder|SearchTag whereSeoEmpId($value)
 * @mixin \Eloquent
 */
class SearchTag extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    public function assignedSeo()
    {
        return $this->belongsTo(User::class, 'seo_emp_id');
    }

}
