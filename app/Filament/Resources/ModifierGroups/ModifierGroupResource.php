<?php

namespace App\Filament\Resources\ModifierGroups;

use App\Filament\Resources\ModifierGroups\Pages;
use App\Models\ModifierGroup;
use BackedEnum;
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

class ModifierGroupResource extends Resource
{
    protected static ?string $model = ModifierGroup::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Modifier Groups';

    protected static ?string $modelLabel = 'Modifier Group';

    protected static ?string $pluralModelLabel = 'Modifier Groups';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Modifier Group')
                ->schema([
                    Select::make('business_id')
                        ->label('Business')
                        ->relationship('business', 'name')
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
                        ->unique(ignoreRecord: true),

                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('is_required')
                        ->label('Required')
                        ->default(false),

                    TextInput::make('min_selections')
                        ->label('Minimum Selections')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(0),

                    TextInput::make('max_selections')
                        ->label('Maximum Selections')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->default(1),

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
                    ->label('Group')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                TextColumn::make('min_selections')
                    ->label('Min'),

                TextColumn::make('max_selections')
                    ->label('Max'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('business_id')
                    ->label('Business')
                    ->relationship('business', 'name'),

                TernaryFilter::make('is_required')
                    ->label('Required'),

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
            'index' => Pages\ListModifierGroups::route('/'),
            'create' => Pages\CreateModifierGroup::route('/create'),
            'edit' => Pages\EditModifierGroup::route('/{record}/edit'),
        ];
    }
}