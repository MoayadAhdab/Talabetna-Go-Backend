<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages;
use App\Models\Customer;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-users';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->disk('public')
                            ->directory('customers')
                            ->imageEditor(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Toggle::make('is_verified')
                            ->label('Verified')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Customer Statistics')
                    ->schema([
                        TextInput::make('total_orders')
                            ->label('Total Orders')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->disabled(),

                        TextInput::make('total_spent')
                            ->label('Total Spent')
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
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('total_orders')
                    ->label('Orders')
                    ->sortable(),

                TextColumn::make('total_spent')
                    ->label('Spent')
                    ->sortable(),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_verified')
                    ->label('Verified'),

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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}