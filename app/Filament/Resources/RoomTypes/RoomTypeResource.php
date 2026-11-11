<?php

namespace App\Filament\Resources\RoomTypes;

use App\Enums\RoomCategory;
use App\Filament\Resources\RoomTypes\Pages\ManageRoomTypes;
use App\Models\RoomType;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class RoomTypeResource extends Resource
{
    protected static ?string $model = RoomType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Rooms';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('category')
                    ->options(RoomCategory::class)
                    ->required(),
                TextInput::make('vibe_tag')
                    ->placeholder('Riverview'),
                TextInput::make('tagline')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TagsInput::make('amenities')
                    ->placeholder('Add amenity')
                    ->columnSpanFull(),
                TextInput::make('base_price')
                    ->label('Base price per night')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->suffix('BDT'),
                TextInput::make('max_guests')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->default(2),
                TextInput::make('image_path')
                    ->label('Image path')
                    ->helperText('Path or URL of the photo, e.g. /images/rooms/riverview.jpg. Uploads come later.'),
                TextInput::make('sort_order')
                    ->required()
                    ->integer()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->description(fn (RoomType $record) => $record->tagline),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('base_price')
                    ->label('Base / night')
                    ->numeric()
                    ->suffix(' BDT')
                    ->sortable(),
                TextColumn::make('max_guests')
                    ->label('Max guests'),
                TextColumn::make('rooms_count')
                    ->counts('rooms')
                    ->label('Rooms'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoomTypes::route('/'),
        ];
    }
}
