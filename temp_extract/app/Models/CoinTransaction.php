<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CoinTransaction
 *
 * @property int $id
 * @property string $user_id
 * @property string|null $refered_user
 * @property string $reason
 * @property int $debited
 * @property int $credited
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereCredited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereDebited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereReferedUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinTransaction whereUserId($value)
 * @mixin \Eloquent
 */
class CoinTransaction extends Model
{
	protected $table = 'coin_transaction';
	protected $connection = 'mysql';
    use HasFactory;
}
