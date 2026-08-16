<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required(),
                Select::make('cart_id')
                    ->relationship('cart', 'id'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('delivery_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_method'),
                Textarea::make('customer_note')
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('delivery_address')
                    ->required(),
                TextInput::make('branch_snapshot')
                    ->required(),
                TextInput::make('settings'),
            ]);
    }
}
