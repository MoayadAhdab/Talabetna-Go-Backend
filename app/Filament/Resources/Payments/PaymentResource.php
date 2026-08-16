<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages;
use App\Models\Payment;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $modelLabel = 'Payment';

    protected static ?string $pluralModelLabel = 'Payments';

    protected static ?int $navigationSort = 14;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('method')
                            ->label('Method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'online' => 'Online',
                                'wallet' => 'Wallet',
                            ])
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        TextInput::make('currency')
                            ->label('Currency')
                            ->default('USD')
                            ->required(),

                        TextInput::make('transaction_id')
                            ->label('Transaction ID'),

                        TextInput::make('reference')
                            ->label('Reference'),

                        TextInput::make('paid_at')
                            ->label('Paid At')
                            ->disabled(),

                        Textarea::make('failure_reason')
                            ->label('Failure Reason')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Gateway Data')
                    ->schema([
                        KeyValue::make('gateway_response')
                            ->label('Gateway Response')
                            ->disabled()
                            ->columnSpanFull(),

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
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Currency'),

                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'online' => 'Online',
                        'wallet' => 'Wallet',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}