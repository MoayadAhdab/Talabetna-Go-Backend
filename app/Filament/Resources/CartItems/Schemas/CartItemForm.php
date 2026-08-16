<?php

namespace App\Filament\Resources\CartItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CartItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cart_id')
                    ->relationship('cart', 'id')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('modifiers_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('selected_modifiers'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
