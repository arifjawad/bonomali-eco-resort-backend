<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingArrivals extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Arrivals in the next 7 days';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Booking::query()
                ->with(['guest', 'room.roomType'])
                ->notCancelled()
                ->whereBetween('check_in', [today()->toDateString(), today()->addDays(7)->toDateString()]))
            ->defaultSort('check_in')
            ->paginated(false)
            ->recordUrl(fn (Booking $record) => BookingResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('No arrivals in the next 7 days')
            ->columns([
                TextColumn::make('check_in')
                    ->date('D, j M'),
                TextColumn::make('guest.name')
                    ->description(fn (Booking $record) => $record->guest->phone),
                TextColumn::make('room.name')
                    ->label('Room')
                    ->description(fn (Booking $record) => $record->room->roomType->name),
                TextColumn::make('nights')
                    ->state(fn (Booking $record) => $record->nights()),
                TextColumn::make('adults')
                    ->label('Guests')
                    ->formatStateUsing(fn (Booking $record) => $record->adults + $record->children),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('reference'),
            ]);
    }
}
