<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('avatar'),
                TextInput::make('vehicle_type'),
                TextInput::make('vehicle_number'),
                TextInput::make('status')
                    ->required()
                    ->default('offline'),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_verified')
                    ->required(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                DateTimePicker::make('last_location_at'),
                TextInput::make('settings'),
            ]);
    }
}
