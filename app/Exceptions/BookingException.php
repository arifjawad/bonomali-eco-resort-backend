<?php

namespace App\Exceptions;

use App\Enums\BookingStatus;
use App\Enums\InquiryStatus;
use DomainException;

/**
 * A booking rule was broken. The message is safe to show to staff.
 */
class BookingException extends DomainException
{
    public static function missingDates(): self
    {
        return new self('Check-in and check-out dates are required.');
    }

    public static function invalidDates(): self
    {
        return new self('Check-out must be at least one day after check-in.');
    }

    public static function roomInactive(): self
    {
        return new self('This room is out of service.');
    }

    public static function roomNotFree(string $roomName): self
    {
        return new self("{$roomName} is already booked for some of these nights.");
    }

    public static function tooManyGuests(int $maxGuests): self
    {
        return new self("This room takes at most {$maxGuests} guests.");
    }

    public static function cannotMove(BookingStatus $from, BookingStatus $to): self
    {
        return new self("A {$from->getLabel()} booking cannot be marked {$to->getLabel()}.");
    }

    public static function inquiryClosed(InquiryStatus $status): self
    {
        return new self("This inquiry is already {$status->getLabel()}.");
    }
}
