<?php

namespace App\Filament\Resources\Carts;

use App\Filament\Resources\Carts\Pages;
use App\Models\Cart;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Carts';

    protected static ?string $modelLabel = 'Cart';

    protected static ?string $pluralModelLabel = 'Carts';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cart Information')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'converted' => 'Converted',
                                'abandoned' => 'Abandoned',
                            ])
                            ->default('active')
                            ->required(),

                        TextInput::make('items_count')
                            ->label('Items Count')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->disabled(),

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        TextInput::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        TextInput::make('discount')
                            ->label('Discount')
                            ->numeric()
                            ->default(0),

                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->sortable(),

                TextColumn::make('delivery_fee')
                    ->label('Delivery')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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
            'index' => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit' => Pages\EditCart::route('/{record}/edit'),
        ];
    }
}