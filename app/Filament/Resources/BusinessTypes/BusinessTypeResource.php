<?php

namespace App\Filament\Resources\BusinessTypes;

use App\Filament\Resources\BusinessTypes\Pages;
use App\Models\BusinessType;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BusinessTypeResource extends Resource
{
    protected static ?string $model = BusinessType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Business Types';

    protected static ?string $modelLabel = 'Business Type';

    protected static ?string $pluralModelLabel = 'Business Types';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business Type Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('icon')
                            ->label('Icon')
                            ->placeholder('heroicon-o-building-storefront')
                            ->maxLength(255),

                        FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('business-types')
                            ->imageEditor(),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),

                Section::make('Settings')
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
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),


                IconColumn::make('status')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Status'),
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
            'index' => Pages\ListBusinessTypes::route('/'),
            'create' => Pages\CreateBusinessType::route('/create'),
            'edit' => Pages\EditBusinessType::route('/{record}/edit'),
        ];
    }
}