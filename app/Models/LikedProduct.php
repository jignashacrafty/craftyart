<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LikedProduct
 *
 * @property int $id
 * @property string $user_id
 * @property string $product_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LikedProduct whereUserId($value)
 * @mixin \Eloquent
 */
class LikedProduct extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
}