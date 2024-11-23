<?php

namespace App\Enums;

use \Spatie\Enum\Enum;

/**
 * @method static self LomeCinkasse()
 * @method static self LomeKpalime()
 * @method static self LomeAneho()
 * @method static self KpalimeLome()
 * @method static self AnehoLome()
 */
class RoutingsClass extends Enum
{

    protected static function labels(): array
    {
        return [
            'LomeCinkasse' => 'Lomé-Cinkassé',
            'LomeKpalime' => 'Lomé-Kpalimé',
            'LomeAneho' => 'Lomé-Aneho',
            'KpalimeLome' => 'Kpalime-Lomé',
            'AnehoLome' => 'Aneho-Lomé',
        ];
    }
}