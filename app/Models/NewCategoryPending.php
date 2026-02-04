<?php

namespace App\Models;

use App\Http\Controllers\Api\HelperController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\NewCategoryPending
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewCategoryPending newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewCategoryPending newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewCategoryPending query()
 * @mixin \Eloquent
 */
class NewCategoryPending extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
}
