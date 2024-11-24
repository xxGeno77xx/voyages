<?php

namespace App\Filament\Resources\VoyageResource\Pages;

use Filament\Actions;
use Filament\Actions\StaticAction;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VoyageResource;
use Filament\Forms\Components\TextInput;

class ListVoyages extends ListRecords
{
    protected static string $resource = VoyageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
