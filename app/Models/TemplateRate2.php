<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\TemplateRate
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $value
 * @property int $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|TemplateRate2 newModelQuery()
 * @method static Builder|TemplateRate2 newQuery()
 * @method static Builder|TemplateRate2 query()
 * @method static Builder|TemplateRate2 whereCreatedAt($value)
 * @method static Builder|TemplateRate2 whereId($value)
 * @method static Builder|TemplateRate2 whereName($value)
 * @method static Builder|TemplateRate2 whereType($value)
 * @method static Builder|TemplateRate2 whereUpdatedAt($value)
 * @method static Builder|TemplateRate2 whereValue($value)
 * @mixin \Eloquent
 */
class TemplateRate2 extends Model
{
    protected $table = 'template_rates';
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'value',
        'type'
    ];

    public static function getDefaultValue(): array
    {
        return [
            'inr' => [
                'base_price' => 99,
                'page_price' => 100,
                'max_price' => 399,
                'editor_choice'=>0,
                'animation'=>0
            ],
            'usd' => [
                'base_price' => 54,
                'page_price' => 20,
                'max_price' => 10,
                'editor_choice'=>0,
                'animation'=>0
            ],
        ];
    }

    public static function getCaricatureDefaultValue(): array
    {
        return [
            'inr' => [
                'base_price' => 99,
                'head_price' => 100,
                'max_price' => 399,
                'editor_choice'=>0,
                'animation'=>0
            ],
            'usd' => [
                'base_price' => 54,
                'head_price' => 20,
                'max_price' => 10,
                'editor_choice'=>0,
                'animation'=>0
            ],
        ];
    }



    public static function getRates($name,$isCaricature = false)
    {
        $rate = TemplateRate::where('name', $name)->pluck('value')->first();

        if ($rate) {
            $decodedRate = json_decode($rate, true);

            if (self::checkRateStructure($isCaricature ? self::getCaricatureDefaultValue() : self::getDefaultValue(), $decodedRate)) {
                return $decodedRate;
            }
        }

        return  $isCaricature ? self::getCaricatureDefaultValue() : self::getDefaultValue();
    }

    private static function checkRateStructure(array $default, array $data): bool
    {
        foreach ($default as $currency => $fields) {
            if (!isset($data[$currency]) || !is_array($data[$currency])) {
                return false;
            }

            foreach ($fields as $key => $value) {
                if (!array_key_exists($key, $data[$currency])) {
                    return false;
                }
            }
        }
        return true;
    }
}