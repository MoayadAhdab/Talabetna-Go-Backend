<?php

namespace App\Filament\Resources\Deliveries;

use App\Filament\Resources\Deliveries\Pages;
use App\Models\Delivery;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-map';

    protected static ?string $navigationLabel = 'Deliveries';

    protected static ?string $modelLabel = 'Delivery';

    protected static ?string $pluralModelLabel = 'Deliveries';

    protected static ?int $navigationSort = 16;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Delivery')
                ->schema([
                    Select::make('order_id')
                        ->label('Order')
                        ->relationship('order', 'order_number')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('driver_id')
                        ->label('Driver')
                        ->relationship('driver', 'name')
                        ->searchable()
                        ->preload(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'assigned' => 'Assigned',
                            'picked_up' => 'Picked Up',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                            'failed' => 'Failed',
                        ])
                        ->required(),

                    TextInput::make('delivery_fee')
                        ->label('Delivery Fee')
                        ->numeric()
                        ->disabled(),

                    Textarea::make('failure_reason')
                        ->label('Failure Reason')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Location')
                ->schema([
                    TextInput::make('pickup_latitude')
                        ->label('Pickup Latitude')
                        ->numeric()
                        ->disabled(),

                    TextInput::make('pickup_longitude')
                        ->label('Pickup Longitude')
                        ->numeric()
                        ->disabled(),

                    TextInput::make('delivery_latitude')
                        ->label('Delivery Latitude')
                        ->numeric()
                        ->disabled(),

                    TextInput::make('delivery_longitude')
                        ->label('Delivery Longitude')
                        ->numeric()
                        ->disabled(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('delivery_fee')
                    ->label('Fee')
                    ->sortable(),

                TextColumn::make('assigned_at')
                    ->label('Assigned')
                    ->dateTime('Y-m-d H:i'),

                TextColumn::make('delivered_at')
                    ->label('Delivered')
                    ->dateTime('Y-m-d H:i'),
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
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }
}