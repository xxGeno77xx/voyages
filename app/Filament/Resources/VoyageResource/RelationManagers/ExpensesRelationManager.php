<?php

namespace App\Filament\Resources\VoyageResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ExpensesCategorie;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';
    
    protected static bool $isLazy = false;
    protected static ?string $title = 'Dépenses';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                ->schema([
                    DatePicker::make("date")
                        ->required(),

                    Select::make("expense_category_id")
                        ->label(__("Catégorie"))
                        ->native(false)
                        ->createOptionForm(fn(Form $form) => [TextInput::make("label")->label(__("intitulé"))])
                        ->createOptionUsing(fn(array $data) => ExpensesCategorie::create($data)->getKey())
                        ->preload()
                        ->options(ExpensesCategorie::pluck("label", "id")),

                    Select::make("supplier_id")
                        ->label(__("Fournisseur"))
                        ->native(false)
                        ->preload()
                        ->options(Supplier::pluck("raison_sociale", "id")),

                    TextInput::make("amount")
                        ->label(__("Montant"))
                        ->numeric()
                        ->required(),

                    TextInput::make("description")
                        ->required(),
                    TextInput::make("justification")
                        ->required(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('expense_category_id')
                ->label(__("Catégorie"))
                    ->formatStateUsing(fn($state) => ExpensesCategorie::find($state)->label )
                    ->badge(),

                Tables\Columns\TextColumn::make('date')->date("d M Y"),

                Tables\Columns\TextColumn::make('amount')
                ->label(__("Montant"))
                ->numeric(0,null, '.'),

                Tables\Columns\TextColumn::make('supplier_id')
                ->label(__("Fournisseur"))
                ->formatStateUsing(fn($state) => Supplier::find($state)->raison_sociale),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
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
}
