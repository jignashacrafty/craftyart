<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BonusPackage
 *
 * @property int $id
 * @property string $string_id
 * @property string $bonus_code
 * @property string $inr_price
 * @property string $usd_price
 * @property string $additional_day
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage query()
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereAdditionalDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereBonusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereInrPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BonusPackage whereUsdPrice($value)
 * @mixin \Eloquent
 */
class BonusPackage extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
	protected $table = 'bonus_package';

    protected $fillable = [
        'string_id',
        'bonus_code',
        'inr_price',
        'usd_price',
        'additional_day',
    ];
}