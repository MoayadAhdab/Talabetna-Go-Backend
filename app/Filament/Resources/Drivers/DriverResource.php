<?php

namespace App\Filament\Resources\Drivers;

use App\Filament\Resources\Drivers\Pages;
use App\Models\Driver;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Drivers';

    protected static ?string $modelLabel = 'Driver';

    protected static ?string $pluralModelLabel = 'Drivers';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Driver Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required(),

                    TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('email')
                        ->label('Email')
                        ->email(),

                    FileUpload::make('avatar')
                        ->label('Avatar')
                        ->image()
                        ->disk('public')
                        ->directory('drivers'),

                    Select::make('vehicle_type')
                        ->label('Vehicle Type')
                        ->options([
                            'motorcycle' => 'Motorcycle',
                            'car' => 'Car',
                            'scooter' => 'Scooter',
                            'bicycle' => 'Bicycle',
                        ]),

                    TextInput::make('vehicle_number')
                        ->label('Vehicle Number'),
                ])
                ->columns(2),

            Section::make('Driver Status')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'offline' => 'Offline',
                            'available' => 'Available',
                            'busy' => 'Busy',
                        ])
                        ->default('offline')
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Toggle::make('is_verified')
                        ->label('Verified')
                        ->default(false),
                ])
                ->columns(3),

            Section::make('Location')
                ->schema([
                    TextInput::make('latitude')
                        ->numeric(),

                    TextInput::make('longitude')
                        ->numeric(),

                    KeyValue::make('settings')
                        ->label('Settings')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('vehicle_type')
                    ->label('Vehicle')
                    ->badge(),

                TextColumn::make('vehicle_number')
                    ->label('Vehicle Number'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'offline' => 'Offline',
                        'available' => 'Available',
                        'busy' => 'Busy',
                    ]),

                TernaryFilter::make('is_verified')
                    ->label('Verified'),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}