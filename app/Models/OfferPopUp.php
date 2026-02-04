<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OfferPopUp
 *
 * @property int $id
 * @property int $enable_offer
 * @property int $duration
 * @property int $frequency_duration
 * @property int $force_show_duration
 * @property int $enable_force
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereEnableForce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereEnableOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereForceShowDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereFrequencyDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfferPopUp whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OfferPopUp extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'offer_popup';

    protected $fillable = [
        'enable_offer',
        'duration',
        'frequency_duration',
        'force_show_duration',
        'enable_force',
    ];
}
