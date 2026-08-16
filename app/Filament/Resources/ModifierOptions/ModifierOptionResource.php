<?php

namespace App\Filament\Resources\ModifierOptions;

use App\Filament\Resources\ModifierOptions\Pages;
use App\Models\ModifierOption;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ModifierOptionResource extends Resource
{
    protected static ?string $model = ModifierOption::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'Modifier Options';

    protected static ?string $modelLabel = 'Modifier Option';

    protected static ?string $pluralModelLabel = 'Modifier Options';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Option Information')
                ->schema([
                    Select::make('modifier_group_id')
                        ->label('Modifier Group')
                        ->relationship('modifierGroup', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set): void {
                            if (filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('price')
                        ->label('Additional Price')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->default(0)
                        ->required(),

                    Toggle::make('is_default')
                        ->label('Default')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(0),
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
                    ->label('Option')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('modifierGroup.name')
                    ->label('Group')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_default')
                    ->label('Default'),

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
            'index' => Pages\ListModifierOptions::route('/'),
            'create' => Pages\CreateModifierOption::route('/create'),
            'edit' => Pages\EditModifierOption::route('/{record}/edit'),
        ];
    }
}