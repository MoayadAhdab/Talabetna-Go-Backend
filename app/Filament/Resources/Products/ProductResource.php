<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Support\Str;
use App\Models\ModifierGroup;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Product';

    protected static ?string $pluralModelLabel = 'Products';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->schema([
                        Select::make('business_id')
                            ->label('Business')
                            ->relationship('business', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Product Name')
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

                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(100),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(5)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Product Image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imageEditor(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])
                    ->columns(2),

              Section::make('Product Settings')
    ->schema([
        TextInput::make('preparation_time_minutes')
            ->label('Preparation Time (Minutes)')
            ->numeric()
            ->integer()
            ->minValue(0),

        TextInput::make('sort_order')
            ->label('Sort Order')
            ->numeric()
            ->integer()
            ->minValue(0)
            ->default(0),

        Toggle::make('is_available')
            ->label('Available')
            ->default(true),

        Toggle::make('is_featured')
            ->label('Featured')
            ->default(false),

        Toggle::make('is_active')
            ->label('Active')
            ->default(true),

        Select::make('modifierGroups')
            ->label('Modifier Groups')
            ->multiple()
            ->relationship(
                name: 'modifierGroups',
                titleAttribute: 'name'
            )
            ->searchable()
            ->preload()
            ->columnSpanFull(),
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
                    ->circular(),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->label('Sale Price')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
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

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                TernaryFilter::make('is_available')
                    ->label('Available'),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}