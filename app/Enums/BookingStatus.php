<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';

    /**
     * Statuses this one may move to. Checked out and cancelled are final.
     *
     * @return array<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::CheckedIn, self::Cancelled],
            self::CheckedIn => [self::CheckedOut],
            self::CheckedOut, self::Cancelled => [],
        };
    }

    public function canMoveTo(self $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }

    public function isFinal(): bool
    {
        return $this->nextStatuses() === [];
    }

    /**
     * Statuses that count as money the resort expects (for revenue figures).
     *
     * @return array<self>
     */
    public static function confirmedStatuses(): array
    {
        return [self::Confirmed, self::CheckedIn, self::CheckedOut];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::CheckedIn => 'Checked in',
            self::CheckedOut => 'Checked out',
            default => ucfirst($this->value),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::CheckedIn => 'success',
            self::CheckedOut => 'gray',
            self::Cancelled => 'danger',
        };
    }
}
