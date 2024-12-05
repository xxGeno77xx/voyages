<?php

namespace App\Enums;

use \Spatie\Enum\Enum;

/**
 * @method static self Lome_Cinkasse()
 * @method static self Lome_Kpalime()
 * @method static self Lome_Aneho()
 * @method static self Kpalime_Lome()
 * @method static self Aneho_Lome()
 */
class RoutingsClass extends Enum
{

    protected static function values(): array
    {
        return [
            'Lome_Cinkasse' => 'Lomé-Cinkassé',
            'Lome_Kpalime' => 'Lomé-Kpalimé',
            'Lome_Aneho' => 'Lomé-Aneho',
            'Kpalime_Lome' => 'Kpalime-Lomé',
            'Aneho_Lome' => 'Aneho-Lomé',
        ];
    }
}