<?php

namespace App\Filament\Resources\Guests\RelationManagers;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Booking history on the guest page. Read-only: bookings are made on the Bookings screen.
 */
class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $title = 'Booking history';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->defaultSort('check_in', 'desc')
            ->recordUrl(fn (Booking $record) => BookingResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('reference'),
                TextColumn::make('room.name')
                    ->label('Room'),
                TextColumn::make('check_in')
                    ->date(),
                TextColumn::make('check_out')
                    ->date(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->suffix(' BDT'),
            ]);
    }
}
