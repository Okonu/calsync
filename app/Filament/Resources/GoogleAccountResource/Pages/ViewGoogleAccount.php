<?php

namespace App\Filament\Resources\GoogleAccountResource\Pages;

use App\Filament\Resources\GoogleAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGoogleAccount extends ViewRecord
{
    protected static string $resource = GoogleAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
