<?php

namespace App\Enums;

enum TemplateFieldType: string
{
    case PROMO_CODE = 'promo_code';

    public static function labels(): array
    {
        return [
            self::PROMO_CODE->value => 'Promo Code',
        ];
    }
}
