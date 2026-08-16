<?php

namespace App\Filament\Resources\Deliveries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required(),
                Select::make('driver_id')
                    ->relationship('driver', 'name'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('assigned_at'),
                DateTimePicker::make('picked_up_at'),
                DateTimePicker::make('out_for_delivery_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('failed_at'),
                Textarea::make('failure_reason')
                    ->columnSpanFull(),
                TextInput::make('pickup_latitude')
                    ->numeric(),
                TextInput::make('pickup_longitude')
                    ->numeric(),
                TextInput::make('delivery_latitude')
                    ->numeric(),
                TextInput::make('delivery_longitude')
                    ->numeric(),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('settings'),
            ]);
    }
}
