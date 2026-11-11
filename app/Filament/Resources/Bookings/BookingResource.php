<?php

namespace App\Filament\Resources\Bookings;

use App\Enums\BookingStatus;
use App\Enums\Source;
use App\Exceptions\BookingException;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Filament\Resources\Guests\GuestResource;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Services\BookingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Manual bookings. Saving and status changes go through BookingService,
 * which checks availability and computes the total.
 */
class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('guest_id')
                    ->label('Guest')
                    ->relationship('guest', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Guest $record) => "{$record->name} ({$record->phone})")
                    ->searchable(['name', 'phone'])
                    ->preload()
                    ->createOptionForm(GuestResource::guestFields())
                    ->required(),
                Select::make('room_id')
                    ->label('Room')
                    ->options(fn (?Booking $record) => static::activeRoomOptions($record?->room_id))
                    ->live()
                    ->required(),
                DatePicker::make('check_in')
                    ->live()
                    ->required(),
                DatePicker::make('check_out')
                    ->live()
                    ->after('check_in')
                    ->required(),
                TextInput::make('adults')
                    ->integer()
                    ->minValue(1)
                    ->default(2)
                    ->required(),
                TextInput::make('children')
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Select::make('source')
                    ->options(Source::class)
                    ->default(Source::WhatsApp)
                    ->required(),
                TextEntry::make('total_preview')
                    ->label('Total')
                    ->state(fn (Get $get) => static::totalPreview($get))
                    ->helperText('Room price for the nights, seasonal rates included. Saved when you save.'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('check_in', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('guest.name')
                    ->description(fn (Booking $record) => $record->guest->phone)
                    ->searchable(['name', 'phone']),
                TextColumn::make('room.name')
                    ->label('Room')
                    ->description(fn (Booking $record) => $record->room->roomType->name),
                TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->suffix(' BDT')
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->label('Entered by')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['guest', 'room.roomType']))
            ->filters([
                SelectFilter::make('status')
                    ->options(BookingStatus::class)
                    ->multiple(),
                SelectFilter::make('room_id')
                    ->label('Room')
                    ->relationship('room', 'name'),
                static::stayDatesFilter(),
            ])
            ->recordActions([
                ActionGroup::make([
                    static::statusAction('confirm', 'Confirm', BookingStatus::Confirmed, Heroicon::OutlinedCheck),
                    static::statusAction('checkIn', 'Check in', BookingStatus::CheckedIn, Heroicon::OutlinedArrowRightEndOnRectangle),
                    static::statusAction('checkOut', 'Check out', BookingStatus::CheckedOut, Heroicon::OutlinedArrowLeftStartOnRectangle),
                    static::statusAction('cancel', 'Cancel', BookingStatus::Cancelled, Heroicon::OutlinedXMark)
                        ->requiresConfirmation(),
                ]),
                EditAction::make(),
                ViewAction::make()
                    ->visible(fn (Booking $record) => $record->status->isFinal()),
            ]);
    }

    /**
     * Active rooms as "R-101 (Premium Riverview AC Suite)", plus the booking's
     * current room when it has since gone out of service.
     *
     * @return array<int, string>
     */
    public static function activeRoomOptions(?int $currentRoomId = null): array
    {
        return Room::with('roomType')
            ->where(fn (Builder $query) => $query->where('is_active', true)->orWhereKey($currentRoomId))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Room $room) => [$room->id => $room->label()])
            ->all();
    }

    /**
     * One table action per status change. $method is the BookingService method to call.
     */
    private static function statusAction(string $method, string $label, BookingStatus $target, Heroicon $icon): Action
    {
        return Action::make($method)
            ->label($label)
            ->icon($icon)
            ->color($target->getColor())
            ->visible(fn (Booking $record) => $record->status->canMoveTo($target))
            ->action(function (Booking $record, BookingService $bookings) use ($method, $label) {
                try {
                    $bookings->{$method}($record);
                    Notification::make()->success()->title("{$record->reference}: {$label} done")->send();
                } catch (BookingException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                }
            });
    }

    /**
     * Bookings with at least one night inside the chosen dates.
     */
    private static function stayDatesFilter(): Filter
    {
        return Filter::make('stay_dates')
            ->schema([
                DatePicker::make('from')->label('Staying from'),
                DatePicker::make('until')->label('Staying until'),
            ])
            ->query(fn (Builder $query, array $data) => $query
                ->when($data['from'], fn (Builder $query, string $from) => $query->whereDate('check_out', '>', $from))
                ->when($data['until'], fn (Builder $query, string $until) => $query->whereDate('check_in', '<=', $until)));
    }

    private static function totalPreview(Get $get): string
    {
        $total = app(BookingService::class)->quote(
            filled($get('room_id')) ? (int) $get('room_id') : null,
            $get('check_in'),
            $get('check_out'),
        );

        return $total === null ? 'Pick a room and dates' : number_format($total).' BDT';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'view' => ViewBooking::route('/{record}'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
