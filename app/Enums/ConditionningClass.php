<?php

namespace App\Enums;

use \Spatie\Enum\Enum;

/**
 * @method static self Balot()
 * @method static self Sac()
 * @method static self Panier()
 * @method static self Bidon()
 * @method static self Vrac()
 */
class ConditionningClass extends Enum
{

    protected static function labels(): array
    {
        return [
            'En_vrac' => 'En vrac',
        ];
    }
}