<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                TextInput::make('max_discount')
                    ->numeric(),
                TextInput::make('minimum_order_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('usage_limit')
                    ->numeric(),
                TextInput::make('usage_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('per_customer_limit')
                    ->numeric(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('expires_at'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('settings'),
            ]);
    }
}
