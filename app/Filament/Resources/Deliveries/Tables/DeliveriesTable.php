<?php

namespace App\Filament\Resources\Deliveries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('assigned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('picked_up_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('out_for_delivery_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('failed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('pickup_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pickup_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
