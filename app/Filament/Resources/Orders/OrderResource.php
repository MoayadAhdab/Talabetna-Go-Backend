<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages;
use App\Models\Order;
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

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $pluralModelLabel = 'Orders';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled(),

                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),

                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),

                        Select::make('cart_id')
                            ->label('Cart')
                            ->relationship('cart', 'id')
                            ->disabled(),

                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),

                        Select::make('delivery_status')
                            ->label('Delivery Status')
                            ->options([
                                'pending' => 'Pending',
                                'assigned' => 'Assigned',
                                'picked_up' => 'Picked Up',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                                'failed' => 'Failed',
                            ])
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'online' => 'Online',
                            ])
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled(),

                        TextInput::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->disabled(),

                        TextInput::make('discount')
                            ->label('Discount')
                            ->disabled(),

                        TextInput::make('tax')
                            ->label('Tax')
                            ->disabled(),

                        TextInput::make('total')
                            ->label('Total')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Customer Note')
                    ->schema([
                        Textarea::make('customer_note')
                            ->label('Customer Note')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Delivery Address Snapshot')
                    ->schema([
                        KeyValue::make('delivery_address')
                            ->label('Address')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Branch Snapshot')
                    ->schema([
                        KeyValue::make('branch_snapshot')
                            ->label('Branch')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Order Metadata')
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Settings')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable(),

                TextColumn::make('delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),

                TextColumn::make('total')
                    ->label('Total')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'rejected' => 'Rejected',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('delivery_status')
                    ->options([
                        'pending' => 'Pending',
                        'assigned' => 'Assigned',
                        'picked_up' => 'Picked Up',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}