<?php

namespace App\Filament\Pages;

use App\Jobs\SyncGoogleCalendars;
use App\Models\GoogleAccount;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.account-settings';
    protected static ?string $title = 'Account Settings';
    protected static ?string $navigationLabel = 'Account Settings';
    protected static ?int $navigationSort = 0;

    public function getTableQuery(): Builder
    {
        return GoogleAccount::query()->where('user_id', auth()->id());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                ColorColumn::make('color'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        ColorPicker::make('color'),
                        Toggle::make('is_active'),
                    ]),
                Tables\Actions\Action::make('sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function (GoogleAccount $record): void {
                        SyncGoogleCalendars::dispatch($record);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Action::make('connect_google')
                    ->label('Connect Google Account')
                    ->icon('heroicon-o-plus')
                    ->url(route('google.redirect')),
            ]);
    }
}
