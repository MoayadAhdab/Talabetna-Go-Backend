<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages;
use App\Models\Banner;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
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

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banners';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    protected static ?int $navigationSort = 18;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner Information')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255),

                        TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->maxLength(255),

                        FileUpload::make('image')
                            ->label('Desktop Image')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->imageEditor()
                            ->required(),

                        FileUpload::make('mobile_image')
                            ->label('Mobile Image')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->imageEditor(),

                        Select::make('placement')
                            ->label('Placement')
                            ->options([
                                'top' => 'Top',
                            ])
                            ->default('top')
                            ->required(),

                        Select::make('link_type')
                            ->label('Link Type')
                            ->options([
                                'none' => 'None',
                                'merchant' => 'Merchant',
                                'category' => 'Category',
                                'product' => 'Product',
                                'coupon' => 'Coupon',
                                'url' => 'URL',
                            ])
                            ->default('none')
                            ->live()
                            ->required(),

                        TextInput::make('link_value')
                            ->label('Link Value')
                            ->placeholder('Example: baytuna-express / chicken-meal / SAVE10')
                            ->maxLength(255),

                        Select::make('business_id')
                            ->label('Merchant')
                            ->relationship('business', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Schedule')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->native(false),

                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Additional Settings')
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Settings')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->square(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('placement')
                    ->label('Placement')
                    ->badge()
                    ->sortable(),

                TextColumn::make('link_type')
                    ->label('Link')
                    ->badge(),

                TextColumn::make('business.name')
                    ->label('Merchant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('placement')
                    ->options([
                        'top' => 'Top',
                    ]),

                SelectFilter::make('link_type')
                    ->options([
                        'none' => 'None',
                        'merchant' => 'Merchant',
                        'category' => 'Category',
                        'product' => 'Product',
                        'coupon' => 'Coupon',
                        'url' => 'URL',
                    ]),

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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}