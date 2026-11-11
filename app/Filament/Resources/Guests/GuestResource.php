<?php

namespace App\Filament\Resources\Guests;

use App\Enums\Source;
use App\Filament\Resources\Guests\Pages\CreateGuest;
use App\Filament\Resources\Guests\Pages\EditGuest;
use App\Filament\Resources\Guests\Pages\ListGuests;
use App\Filament\Resources\Guests\RelationManagers\BookingsRelationManager;
use App\Models\Guest;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Guests';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components(static::guestFields());
    }

    /**
     * Also used by the "new guest" option on the booking form.
     */
    public static function guestFields(): array
    {
        return [
            TextInput::make('name')
                ->required(),
            TextInput::make('phone')
                ->tel()
                ->required()
                ->helperText('WhatsApp number if they have one.'),
            TextInput::make('email')
                ->email(),
            TextInput::make('address'),
            Select::make('source')
                ->options(Source::class)
                ->default(Source::WhatsApp)
                ->required(),
            TagsInput::make('tags')
                ->placeholder('VIP, corporate, repeat'),
            Textarea::make('notes')
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('source')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('tags')
                    ->badge(),
                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label('Bookings')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options(Source::class),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuests::route('/'),
            'create' => CreateGuest::route('/create'),
            'edit' => EditGuest::route('/{record}/edit'),
        ];
    }
}
