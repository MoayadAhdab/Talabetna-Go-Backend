<?php

namespace App\Filament\Resources\BusinessTypes\Pages;

use App\Filament\Resources\BusinessTypes\BusinessTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessType extends EditRecord
{
    protected static string $resource = BusinessTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
