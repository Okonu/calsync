<?php

namespace App\Filament\Resources\GoogleAccountResource\Pages;

use App\Filament\Resources\GoogleAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoogleAccounts extends ListRecords
{
    protected static string $resource = GoogleAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('connect')
                ->label('Connect Google Account')
                ->icon('heroicon-o-plus')
                ->url(route('google.redirect')),
            Actions\CreateAction::make(),
        ];
    }
}
