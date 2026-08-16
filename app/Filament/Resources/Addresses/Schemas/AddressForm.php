<?php

namespace App\Filament\Resources\Addresses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('contact_name'),
                TextInput::make('contact_phone')
                    ->tel(),
                TextInput::make('address_line')
                    ->required(),
                TextInput::make('building'),
                TextInput::make('floor'),
                TextInput::make('apartment'),
                TextInput::make('city'),
                TextInput::make('area'),
                Textarea::make('delivery_instructions')
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Toggle::make('is_default')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
