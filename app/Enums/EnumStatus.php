<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EnumStatus: string implements HasColor, HasIcon, HasLabel
{
    case Aller = 'aller';

    case Retour = 'retour';

    public function getLabel(): string
    {
        return match ($this) {
            self::Aller => 'Aller',
            self::Retour => 'Retour',

        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Aller => 'success',
            self::Retour => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {

            self::Retour => 'heroicon-m-arrow-path',
            self::Aller => 'heroicon-m-truck', 
        };
    }
}