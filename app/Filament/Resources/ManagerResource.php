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

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                ->schema([
                    TextInput::make("full_name")
                    ->label(__("Nom complet"))
                    ->required()
                    ->unique(ignoreRecord: true),

                    TextInput::make("telephone")
                    ->label(__("Téléphone"))
                    ->prefix("+228"),

                    TextInput::make("gare")
                    ->label(__("Gare"))
                    ->required(),

                    TextInput::make("covered_zone")
                    ->label(__("Zone couverte"))
                    ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("full_name")
                ->label(__("Nom complet"))
                ->searchable(),

                TextColumn::make("telephone")
                ->label(__("Téléphone"))
                ->prefix("+228 ")
                ->badge(),

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
