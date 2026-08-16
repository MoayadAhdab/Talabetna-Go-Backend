<?php

namespace App\Filament\Resources\ModifierOptions\Pages;

use App\Filament\Resources\ModifierOptions\ModifierOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditModifierOption extends EditRecord
{
    protected static string $resource = ModifierOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
