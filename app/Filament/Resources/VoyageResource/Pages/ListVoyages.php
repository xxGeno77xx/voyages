<?php

namespace App\Filament\Resources\VoyageResource\Pages;

use Filament\Actions;
use Filament\Actions\StaticAction;
use Filament\Pages\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VoyageResource;

class ListVoyages extends ListRecords
{
    protected static string $resource = VoyageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
            Action::make('excelExport')
                ->color(Color::Sky)
                ->icon('heroicon-o-document-text')
                ->label(__('Exporter'))
                ->action(function () {
                    Notification::make('notification')
                        ->title('Export')
                        ->color(Color::Sky)
                        ->icon('heroicon-o-document-text')
                        ->body('Fonctionnalité en cours')
                        ->send();
                }),
                Actions\CreateAction::make(),
        ];
    }
}
