<?php

namespace App\Filament\Resources\Addresses;

use App\Filament\Resources\Addresses\Pages;
use App\Models\Address;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Customer Addresses';

    protected static ?string $modelLabel = 'Address';

    protected static ?string $pluralModelLabel = 'Customer Addresses';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('label')
                            ->label('Label')
                            ->placeholder('Home, Work, Office')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->maxLength(255),

                        TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Section::make('Address Information')
                    ->schema([
                        TextInput::make('address_line')
                            ->label('Address')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('building')
                            ->label('Building'),

                        TextInput::make('floor')
                            ->label('Floor'),

                        TextInput::make('apartment')
                            ->label('Apartment'),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(100),

                        TextInput::make('area')
                            ->label('Area')
                            ->maxLength(100),

                        Textarea::make('delivery_instructions')
                            ->label('Delivery Instructions')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step('0.0000001'),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step('0.0000001'),

                        Toggle::make('is_default')
                            ->label('Default Address')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address_line')
                    ->label('Address')
                    ->searchable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area')
                    ->label('Area')
                    ->searchable(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name'),

                TernaryFilter::make('is_default')
                    ->label('Default'),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}