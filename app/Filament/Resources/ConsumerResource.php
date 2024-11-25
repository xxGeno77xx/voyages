<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsumerResource\Pages;
use App\Filament\Resources\ConsumerResource\RelationManagers;
use App\Models\Consumer;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsumerResource extends Resource
{
    protected static ?string $model = Consumer::class;
    protected static ?string $label = 'Clients';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__("Informations"))
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextInput::make("raison_sociale")
                                    ->label(__("Raison sociale")),

                                TextInput::make("telephone")
                                    ->label(__("Téléphone"))
                                    ->prefix("+228")
                                    ->required(),

                                TextInput::make("nom")
                                    ->label(__("Nom")),

                                TextInput::make("prenoms")
                                    ->label(__("Prénoms")),


                                TextInput::make("locality")
                                    ->label(__("Localité"))
                                    ->required(),
                            ])

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("raison_sociale")
                    ->label(__("Raison sociale")),

                TextColumn::make("nom")
                    ->label(__("Nom"))
                    ->placeholder("-"),

                TextColumn::make("prenoms")
                    ->label(__("Prenoms"))
                    ->placeholder("-"),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsumers::route('/'),
            'create' => Pages\CreateConsumer::route('/create'),
            'edit' => Pages\EditConsumer::route('/{record}/edit'),
        ];
    }
}
