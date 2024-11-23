<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use App\Models\Manager;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ManagerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ManagerResource\RelationManagers;

class ManagerResource extends Resource
{
    protected static ?string $model = Manager::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                ->schema([
                    TextInput::make("full_name")
                    ->label(__("Nom complet")),
                    TextInput::make("telephone")
                    ->label(__("Téléphone"))
                    ->prefix("+228"),
                    TextInput::make("gare")
                    ->label(__("Gare")),
                    TextInput::make("covered_zone")
                    ->label(__("Zone couverte")),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("full_name")
                ->label(__("Nom complet")),
                TextColumn::make("telephone")
                ->label(__("Téléphone"))
                ->prefix("+228 "),
                TextColumn::make("gare")
                ->label(__("Gare")),
                TextColumn::make("covered_zone")
                ->label(__("Zone couverte")),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageManagers::route('/'),
        ];
    }
}
