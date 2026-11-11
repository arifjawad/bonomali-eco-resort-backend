<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where a guest, booking or inquiry came from.
 */
enum Source: string implements HasLabel
{
    case WhatsApp = 'whatsapp';
    case WalkIn = 'walk_in';
    case Phone = 'phone';
    case Website = 'website';
    case Facebook = 'facebook';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::WhatsApp => 'WhatsApp',
            self::WalkIn => 'Walk-in',
            default => ucfirst($this->value),
        };
    }
}
