<?php

namespace App\Filament\Resources\GoogleAccountResource\Pages;

use App\Filament\Resources\GoogleAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGoogleAccount extends EditRecord
{
    protected static string $resource = GoogleAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
