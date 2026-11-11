<?php

namespace App\Filament\Resources\Inquiries;

use App\Enums\InquiryStatus;
use App\Enums\Source;
use App\Exceptions\BookingException;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Inquiries\Pages\ManageInquiries;
use App\Models\Inquiry;
use App\Services\InquiryService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Leads from the website, WhatsApp, Facebook etc. "Convert to booking" turns one into a guest + booking.
 */
class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $new = Inquiry::where('status', InquiryStatus::New)->count();

        return $new > 0 ? (string) $new : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                DatePicker::make('check_in'),
                DatePicker::make('check_out')
                    ->after('check_in'),
                TextInput::make('guests')
                    ->integer()
                    ->minValue(1),
                TextInput::make('cottage_preference'),
                Select::make('source')
                    ->options(Source::class)
                    ->default(Source::WhatsApp)
                    ->required(),
                Select::make('status')
                    ->options(InquiryStatus::class)
                    ->default(InquiryStatus::New)
                    ->required(),
                Textarea::make('message')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (Inquiry $record) => $record->phone),
                TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->date(),
                TextColumn::make('guests'),
                TextColumn::make('cottage_preference')
                    ->label('Preference')
                    ->toggleable(),
                TextColumn::make('source')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('booking.reference')
                    ->label('Booking')
                    ->url(fn (Inquiry $record) => $record->booking ? BookingResource::getUrl('edit', ['record' => $record->booking]) : null),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InquiryStatus::class)
                    ->multiple(),
                SelectFilter::make('source')
                    ->options(Source::class),
            ])
            ->recordActions([
                static::convertAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function convertAction(): Action
    {
        return Action::make('convert')
            ->label('Convert to booking')
            ->icon(Heroicon::OutlinedArrowRightCircle)
            ->color('success')
            ->visible(fn (Inquiry $record) => $record->status->isOpen())
            ->fillForm(fn (Inquiry $record) => [
                'check_in' => $record->check_in,
                'check_out' => $record->check_out,
                'adults' => $record->guests ?? 1,
                'children' => 0,
            ])
            ->schema([
                Select::make('room_id')
                    ->label('Room')
                    ->options(fn () => BookingResource::activeRoomOptions())
                    ->required(),
                DatePicker::make('check_in')
                    ->required(),
                DatePicker::make('check_out')
                    ->required()
                    ->after('check_in'),
                TextInput::make('adults')
                    ->integer()
                    ->minValue(1)
                    ->required(),
                TextInput::make('children')
                    ->integer()
                    ->minValue(0)
                    ->required(),
            ])
            ->action(function (Inquiry $record, array $data, Action $action, InquiryService $inquiries) {
                try {
                    $booking = $inquiries->convertToBooking($record, $data, auth()->user());
                } catch (BookingException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                    $action->halt();
                }

                Notification::make()->success()->title("Booking {$booking->reference} created")->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInquiries::route('/'),
        ];
    }
}
