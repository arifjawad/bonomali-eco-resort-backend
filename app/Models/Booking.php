<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\Source;
use Carbon\CarbonInterface;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'reference', 'guest_id', 'room_id', 'check_in', 'check_out', 'adults', 'children',
    'status', 'source', 'total_amount', 'notes', 'created_by',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inquiry(): HasOne
    {
        return $this->hasOne(Inquiry::class);
    }

    public function nights(): int
    {
        return (int) $this->check_in->diffInDays($this->check_out);
    }

    #[Scope]
    protected function notCancelled(Builder $query): void
    {
        $query->where('status', '!=', BookingStatus::Cancelled);
    }

    /**
     * Bookings that hold the room on at least one night from $checkIn up to $checkOut.
     * The check-out day is free, so back-to-back stays do not overlap.
     */
    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $checkIn, CarbonInterface $checkOut): void
    {
        $query->whereDate('check_in', '<', $checkOut->toDateString())
            ->whereDate('check_out', '>', $checkIn->toDateString());
    }

    /**
     * Bookings with a guest sleeping there on the night of $date.
     */
    #[Scope]
    protected function staying(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('check_in', '<=', $date->toDateString())
            ->whereDate('check_out', '>', $date->toDateString());
    }

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'adults' => 'integer',
            'children' => 'integer',
            'status' => BookingStatus::class,
            'source' => Source::class,
            'total_amount' => 'integer',
        ];
    }
}
