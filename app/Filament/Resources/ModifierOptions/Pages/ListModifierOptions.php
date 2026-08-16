<?php

namespace App\Filament\Resources\ModifierOptions\Pages;

use App\Filament\Resources\ModifierOptions\ModifierOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModifierOptions extends ListRecords
{
    protected static string $resource = ModifierOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
