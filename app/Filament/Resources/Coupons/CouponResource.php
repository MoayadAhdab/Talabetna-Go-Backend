<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages;
use App\Models\Coupon;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $modelLabel = 'Coupon';

    protected static ?string $pluralModelLabel = 'Coupons';

    protected static ?int $navigationSort = 17;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Coupon Information')
                ->schema([
                    TextInput::make('code')
    ->label('Code')
    ->required()
    ->unique(ignoreRecord: true)
    ->maxLength(50)
    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : $state)
    ->dehydrateStateUsing(fn ($state) => $state ? strtoupper(trim($state)) : $state),

                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'percentage' => 'Percentage',
                            'fixed' => 'Fixed Amount',
                        ])
                        ->required(),

                    TextInput::make('value')
                        ->label('Value')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->required(),

                    TextInput::make('max_discount')
                        ->label('Maximum Discount')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),

                    TextInput::make('minimum_order_amount')
                        ->label('Minimum Order Amount')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->default(0)
                        ->required(),
                ])
                ->columns(2),

            Section::make('Usage Limits')
                ->schema([
                    TextInput::make('usage_limit')
                        ->label('Total Usage Limit')
                        ->numeric()
                        ->integer()
                        ->minValue(1),

                    TextInput::make('per_customer_limit')
                        ->label('Per Customer Limit')
                        ->numeric()
                        ->integer()
                        ->minValue(1),

                    TextInput::make('usage_count')
                        ->label('Usage Count')
                        ->numeric()
                        ->integer()
                        ->disabled()
                        ->default(0),
                ])
                ->columns(3),

            Section::make('Schedule')
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label('Starts At'),

                    DateTimePicker::make('expires_at')
                        ->label('Expires At'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columns(3),

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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('value')
                    ->label('Value')
                    ->sortable(),

                TextColumn::make('minimum_order_amount')
                    ->label('Min. Order')
                    ->sortable(),

                TextColumn::make('usage_count')
                    ->label('Used')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}