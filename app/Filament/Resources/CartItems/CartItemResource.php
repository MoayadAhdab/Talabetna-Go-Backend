<?php

namespace App\Filament\Resources\CartItems;

use App\Filament\Resources\CartItems\Pages;
use App\Models\CartItem;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'Cart Items';

    protected static ?string $modelLabel = 'Cart Item';

    protected static ?string $pluralModelLabel = 'Cart Items';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cart Item')
                    ->schema([
                        Select::make('cart_id')
                            ->label('Cart')
                            ->relationship('cart', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        TextInput::make('modifiers_price')
                            ->label('Modifiers Price')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        KeyValue::make('selected_modifiers')
                            ->label('Selected Modifiers Snapshot')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('cart.id')
                    ->label('Cart')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable(),

                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->sortable(),

                TextColumn::make('modifiers_price')
                    ->label('Modifiers')
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
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
            'index' => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'edit' => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }
}