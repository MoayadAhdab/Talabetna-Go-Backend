<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                TextInput::make('subtitle'),
                FileUpload::make('image')
                    ->image()
                    ->required(),
                FileUpload::make('mobile_image')
                    ->image(),
                TextInput::make('placement')
                    ->required()
                    ->default('top'),
                TextInput::make('link_type')
                    ->required()
                    ->default('none'),
                TextInput::make('link_value'),
                Select::make('business_id')
                    ->relationship('business', 'name'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('expires_at'),
                TextInput::make('settings'),
            ]);
    }
}
