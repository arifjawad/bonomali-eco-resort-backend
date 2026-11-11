<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RoomCategory: string implements HasLabel
{
    case Ac = 'ac';
    case NonAc = 'non_ac';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ac => 'AC',
            self::NonAc => 'Non-AC',
        };
    }
}
