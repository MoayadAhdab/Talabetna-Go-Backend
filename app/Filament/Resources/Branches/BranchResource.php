<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages;
use App\Models\Branch;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Branches';

    protected static ?string $modelLabel = 'Branch';

    protected static ?string $pluralModelLabel = 'Branches';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Select::make('business_id')
                            ->label('Business')
                            ->relationship('business', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Branch Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if (blank($state)) {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(500),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(100),

                        TextInput::make('area')
                            ->label('Area')
                            ->maxLength(100),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step('0.0000001'),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step('0.0000001'),
                    ])
                    ->columns(2),

                Section::make('Delivery Settings')
                    ->schema([
                        TextInput::make('delivery_radius_km')
                            ->label('Delivery Radius (KM)')
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

                        TextInput::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Working Hours')
                    ->schema([
                        Repeater::make('working_hours')
                            ->label('Working Hours')
                            ->schema([
                                Select::make('day')
                                    ->label('Day')
                                    ->options([
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday',
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                    ])
                                    ->required(),

                                TextInput::make('open')
                                    ->label('Open')
                                    ->placeholder('09:00')
                                    ->required(),

                                TextInput::make('close')
                                    ->label('Close')
                                    ->placeholder('23:00')
                                    ->required(),

                                Toggle::make('is_closed')
                                    ->label('Closed')
                                    ->default(false),
                            ])
                            ->columns(4)
                            ->defaultItems(7)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Branch Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Toggle::make('is_accepting_orders')
                            ->label('Accepting Orders')
                            ->default(true),

                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0),

                        KeyValue::make('settings')
                            ->label('Additional Settings')
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
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area')
                    ->label('Area')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('delivery_fee')
                    ->label('Delivery Fee')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_accepting_orders')
                    ->label('Orders')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('business_id')
                    ->label('Business')
                    ->relationship('business', 'name'),

                SelectFilter::make('city')
                    ->options([
                        'Tripoli' => 'Tripoli',
                        'Beirut' => 'Beirut',
                        'Jounieh' => 'Jounieh',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),

                TernaryFilter::make('is_accepting_orders')
                    ->label('Accepting Orders'),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}