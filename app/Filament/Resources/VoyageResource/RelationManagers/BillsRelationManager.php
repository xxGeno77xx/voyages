<?php

namespace App\Filament\Resources\VoyageResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Bill;
use App\Models\Unit;
use Filament\Tables;
use App\Models\Manager;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Consumer;
use Filament\Forms\Form;
use App\Enums\EnumStatus;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\ObjectNature;
use App\Models\Conditionning;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\DatePicker;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VoyageResource;
use App\Filament\Resources\ManagerResource;
use App\Filament\Resources\ConsumerResource;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BillsRelationManager extends RelationManager
{
    protected static bool $isLazy = false;
    protected static string $relationship = 'bills';
    protected static ?string $title = 'Recettes';

    protected static ?string $icon = 'heroicon-o-newspaper';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
              
                Grid::make(2)
                ->schema([

                    TextInput::make("bill_number")
                        ->label(__("Numéro de facture"))
                        ->required()
                        ->disabled()
                        ->dehydrated(),

                    DatePicker::make("date")
                        ->label(__("Date"))
                        ->required(),

                    Select::make("sender_id")
                        ->label("Expéditeur")
                        ->searchable()
                        ->required()
                        ->options(Consumer::pluck("raison_sociale", "id"))
                        ->preload()
                        ->native(false)
                        ->createOptionForm(fn(Form $form) => ConsumerResource::form($form))
                        ->createOptionUsing(fn(array $data) => Consumer::create($data)->getKey()),

                    Select::make("receiver_id")
                        ->label("Destinataire")
                        ->searchable()
                        ->required()
                        ->preload()
                        ->options(Consumer::pluck("raison_sociale", "id"))
                        ->native(false)
                        ->createOptionForm(fn(Form $form) => ConsumerResource::form($form))
                        ->createOptionUsing(fn(array $data) => Consumer::create($data)->getKey()),

                    Section::make("Objets")
                        ->label(__("objets"))
                        ->collapsible()
                        ->columnSpanFull()
                        ->schema([
                            Repeater::make("objects")
                                ->label(__("objets de la facture"))
                                ->defaultItems(0)
                                ->addActionLabel(__("Ajouter un objet  à la facture"))
                                ->grid(2)
                                ->reorderable(false)
                                ->schema([

                                    Grid::make(2)
                                        ->schema([
                                            Select::make("conditionning_id")
                                                ->label(__("Conditionnement"))
                                                ->options(Conditionning::pluck("label", "id"))
                                                ->searchable()
                                                ->required(),

                                            Select::make("object_nature_id")
                                                ->label(__("Nature"))
                                                ->options(ObjectNature::pluck("label", "id"))
                                                ->searchable()
                                                ->required()
                                                ->createOptionForm(fn(Form $form) => [TextInput::make("label")->label(__("intitulé"))])
                                                ->createOptionUsing(fn(array $data) => ObjectNature::create($data)->getKey()),
                                        ]),


                                    TextInput::make("quantity")
                                        ->label(__("Quantité"))
                                        ->integer()
                                        ->required()
                                        ->numeric()
                                        ->default(1),

                                    Grid::make(3)
                                        ->schema([
                                            

                                            Cluster::make([
                                                TextInput::make("weight")
                                                ->label(__("Poids"))
                                                ->numeric(),
                                               

                                                Select::make("unit_id")
                                                    ->options(Unit::pluck("label", "id"))
                                                    ->native(false)
                                                    ->placeholder(__("Unité"))
                                            ])
                                                ->label(__("Poids")),

                                                 
                                                TextInput::make("volume")
                                                    ->label(__("Volume"))
                                                    ->numeric(),

                                            TextInput::make("unit_price")
                                                ->label(__("Prix unitaire"))
                                                ->integer()
                                                ->required()
                                                ->live(debounce: "1000")
                                                ->afterStateUpdated(function (Get $get, Set $set) {

                                                    $set("sous_total", intval($get("unit_price")) * intval($get("quantity")));
                                                })

                                        ]),
                                    TextInput::make("sous_total")
                                        ->label(__("Prix total"))
                                        ->required(),
                                ])
                        ]),


                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('Calculer le total')
                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                VoyageResource::updateTotals($get, $set);

                                $year = Carbon::parse($get("date"))->format("Y");

                                $managerInitials = Str::upper(substr(Manager::find($get("manager_id"))?->full_name, 0, 3));

                                $billsCount = Bill::count();

                                $set("bill_number", "N° " . $get("sender_id") . $get("receiver_id") . $year . $managerInitials . $billsCount);

                                $set("remaining_amount", ($get("total")?? 0) - ($get("paid_amount") ?? 0));
                            })->icon('heroicon-m-pencil-square')
                            ->iconPosition(IconPosition::After)
                            ->extraAttributes([
                                'class' => 'mx-auto my-8',
                            ])

                    ])->columnSpanFull(),

                ]),
            Grid::make(3)
                ->schema([
                    TextInput::make("total")
                        ->label(__("Total"))
                        ->readOnly()
                        ->required(),
                    TextInput::make("paid_amount")
                        ->label(__("Montant payé"))
                        ->required(),

                    TextInput::make("remaining_amount")
                        ->label(__("Reste à payer"))
                        ->readOnly()
                        ->required(),
                ]),
                
                TextInput::make("observations")
                ->label(__("Observations ")),

               Grid::make(2)
                ->schema([
                    Select::make("manager_id")
                    ->label(__("Manager"))
                    ->options(Manager::pluck("full_name", "id"))
                    ->searchable()
                    ->createOptionForm(fn(Form $form) => ManagerResource::form($form))
                    ->createOptionUsing(fn(array $data) => Manager::create($data)->getKey()),

                TextInput::make("commission_fees")
                    ->label(__("Frais de commission"))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix("FCFA"),
                ])
        ]) ;
    }

    public function table(Table $table): Table
    {
        return $table
        ->deferFilters()
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('bill_number')
                    ->label(__("Numéro de facture")),

                TextColumn::make("date")
                    ->date("d M Y"),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label(__("Montant"))
                    ->summarize(Sum::make())
                    ->numeric(0, null,'.'),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label(__("Reste à payer"))
                    ->numeric(0, null,'.'),

                IconColumn::make('status')
                    ->label(__("Statut"))
                    ->state(fn($record) => $record->remaining_amount == 0  || ($record->remaining_amount<0) ? 'ok' : 'ko')
                    ->icon(fn(string $state): string => $state == 'ok' ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->color(fn(string $state) => match ($state) {
                        'ko' => Color::Yellow,
                        'ok' => 'success',
                    })
            ])
            ->filters([
                Self::lineFilter(),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
 

    public static function lineFilter()
    {
        return Filter::make("line")
        ->form([
            ToggleButtons::make('line')
                                    ->label("Aller ou retour")
                                    ->inline()
                                    ->options(EnumStatus::class),
        ])
        ->query(function (Builder $query, array $data): Builder {
            return $query
                ->when( 
                    $data['line'],
                    fn (Builder $query, $line): Builder => $query->where('line', '=', $line),
                );
        });
    }
}
