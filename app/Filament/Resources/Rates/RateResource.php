<?php

namespace App\Filament\Resources\Rates;

use App\Filament\Resources\Rates\Pages\ManageRates;
use App\Models\Rate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Seasonal prices. A rate replaces the room type's base price on the nights it covers.
 */
class RateResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyBangladeshi;

    protected static string|UnitEnum|null $navigationGroup = 'Rooms';

    protected static ?string $navigationLabel = 'Seasonal rates';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('room_type_id')
                    ->relationship('roomType', 'name')
                    ->required(),
                TextInput::make('name')
                    ->placeholder('Eid peak')
                    ->required(),
                DatePicker::make('starts_on')
                    ->label('First night')
                    ->required(),
                DatePicker::make('ends_on')
                    ->label('Last night')
                    ->required()
                    ->afterOrEqual('starts_on'),
                TextInput::make('price_per_night')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->suffix('BDT'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('roomType.name')
                    ->label('Room type'),
                TextColumn::make('starts_on')
                    ->label('First night')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_on')
                    ->label('Last night')
                    ->date()
                    ->sortable(),
                TextColumn::make('price_per_night')
                    ->label('Per night')
                    ->numeric()
                    ->suffix(' BDT'),
                TextColumn::make('roomType.base_price')
                    ->label('Base')
                    ->numeric()
                    ->suffix(' BDT')
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('room_type_id')
                    ->label('Room type')
                    ->relationship('roomType', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRates::route('/'),
        ];
    }
}
