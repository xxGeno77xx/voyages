<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Bill;
use App\Models\Unit;
use Filament\Tables;
use App\Models\Driver;
use App\Models\Voyage;
use App\Models\Expense;
use App\Models\Manager;
use App\Models\Routing;
use App\Models\Vehicle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Consumer;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\ObjectNature;
use App\Models\Conditionning;
use Filament\Resources\Resource;
use App\Models\ExpensesCategorie;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\DatePicker;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\VehicleResource;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\VoyageResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VoyageResource\RelationManagers;

class VoyageResource extends Resource
{

    protected static ?string $model = Voyage::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make("")
                    ->schema([

                        Wizard::make([
                            Wizard\Step::make('ligne_voyage')
                                ->completedIcon('heroicon-m-hand-thumb-up')
                                ->label(__("Ligne de voyage"))
                                ->schema([

                                    Grid::make(2)
                                        ->schema([

                                            TextInput::make("mission")
                                                ->label(__("Mission"))
                                                ->columnSpanFull()
                                                ->required(),

                                            DatePicker::make("departure")
                                                ->label(__("Départ"))
                                                ->columnSpanFull()
                                                ->required(),

                                            Select::make("driver_id")
                                                ->options(Driver::pluck("full_name", "id"))
                                                ->searchable()
                                                ->required()
                                                ->label(__("Chauffeur"))
                                                ->createOptionForm([
                                                    TextInput::make('full_name')
                                                        ->required()
                                                        ->label(__("Nom complet")),
                                                ])
                                                ->createOptionUsing(function (array $data): int {
                                                    return Driver::create($data)->getKey();
                                                }),
                                            Select::make("ass_driver_id")
                                                ->options(Driver::pluck("full_name", "id"))
                                                ->searchable()
                                                ->preload()
                                                ->label(__("Chauffeur assistant"))
                                                ->createOptionForm([
                                                    TextInput::make('full_name')
                                                        ->required()

                                                        ->label(__("Nom complet")),
                                                ])->createOptionUsing(function (array $data): int {
                                                    return Driver::create($data)->getKey();
                                                }),

                                            Select::make("vehicle_id")
                                                ->label(__("Véhicule"))
                                                ->searchable()
                                                ->getSearchResultsUsing(fn(string $search): array => Vehicle::where('plate_number', 'like', "%{$search}%")->limit(50)->pluck('plate_number', 'id')->toArray())
                                                ->getOptionLabelUsing(fn($value): ?string => Vehicle::find($value)?->plate_number)
                                                ->required()
                                                ->preload(fn() => Vehicle::pluck("plate_number", "id"))
                                                ->createOptionForm(fn(Form $form) => VehicleResource::form($form))
                                                ->createOptionUsing(function (array $data): int {
                                                    return Vehicle::create($data)->getKey();
                                                }),

                                            Select::make("routing_id")
                                                ->label(__("Trajet"))
                                                ->searchable()
                                                ->required()
                                                ->preload()
                                                ->options(Routing::pluck("label", "id"))
                                                ->getSearchResultsUsing(fn(string $search): array => Routing::where('label', 'like', "%{$search}%")->limit(50)->pluck('label', 'id')->toArray())
                                                ->getOptionLabelUsing(fn($value): ?string => Routing::find($value)?->label)
                                                ->required()
                                                ->preload(fn() => Routing::pluck("label", "id"))
                                                ->createOptionForm([
                                                    TextInput::make("label")
                                                        ->label(__("Nom du trajet"))
                                                        ->required()
                                                        ->unique(ignoreRecord: true)
                                                ])
                                                ->createOptionUsing(function (array $data): int {

                                                    $data = [
                                                        ...$data,
                                                        "label" => Str::upper($data["label"])

                                                    ];
                                                    return Routing::create($data)->getKey();
                                                }),
                                        ])
                                ]),

                            Wizard\Step::make('Billing')

                                ->label(__("Factures"))
                                ->schema([


                                    Repeater::make("bills")
                                        ->label(__("Factures"))
                                        ->defaultItems(0)
                                        ->relationship("bills")
                                        ->addActionLabel(__("Ajouter une facture"))
                                        ->reorderable(false)
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

                                                    Select::make("manager_id")
                                                        ->label(__("Manager"))
                                                        ->columnSpanFull()
                                                        ->options(Manager::pluck("full_name", "id"))
                                                        ->searchable()
                                                        ->required()
                                                        ->createOptionForm(fn(Form $form) => ManagerResource::form($form))
                                                        ->createOptionUsing(fn(array $data) => Manager::create($data)->getKey()),



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
                                                                                ->required(),
                                                                        ]),


                                                                    TextInput::make("quantity")
                                                                        ->label(__("Quantité"))
                                                                        ->integer()
                                                                        ->required()
                                                                        ->numeric()
                                                                        ->default(1),

                                                                    Grid::make(3)
                                                                        ->schema([
                                                                            TextInput::make("weight")
                                                                                ->label(__("Poids"))
                                                                                ->numeric()
                                                                                ->required(),

                                                                            Cluster::make([
                                                                                TextInput::make("volume")
                                                                                    ->label(__("Volume"))
                                                                                    ->numeric()
                                                                                    ->required(),

                                                                                Select::make("unit_id")
                                                                                    ->options(Unit::pluck("label", "id"))
                                                                                    ->native(false)
                                                                                    ->placeholder(__("Unité"))
                                                                                    ->required()
                                                                            ])
                                                                                ->label(__("Volume")),

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
                                                                        ->required()
                                                                        ->readOnly(),
                                                                ])
                                                            // ->live()
                                                            // ->afterStateUpdated(function (Get $get, Set $set) {
                                                            //     self::updateTotals($get, $set);
                                                            // }),
                                                        ]),

                                                    TextInput::make("commission_fees")
                                                        ->label(__("Frais de commission"))
                                                        ->numeric()
                                                        ->required()
                                                        ->minValue(0)
                                                        ->suffix("FCFA"),

                                                    TextInput::make("observations")
                                                        ->label(__("Observations ")),

                                                    Forms\Components\Actions::make([
                                                        Forms\Components\Actions\Action::make('Calculer le total')
                                                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                                                self::updateTotals($get, $set);

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
                                                        ->columnSpanFull()
                                                        ->readOnly()
                                                        ->required(),
                                                    TextInput::make("paid_amount")
                                                        ->label(__("Montant payé"))
                                                        ->columnSpanFull()
                                                        ->required(),

                                                    TextInput::make("remaining_amount")
                                                        ->label(__("Reste à payer"))
                                                        ->columnSpanFull()
                                                        ->readOnly()
                                                        ->required(),
                                                ]),
                                        ])
                                ]),

                            Wizard\Step::make('expenses')
                                ->label(__("Dépenses"))
                                ->schema([
                                    Repeater::make("objects")
                                        ->label(__("Dépenses"))
                                        ->relationship("expenses")
                                        ->defaultItems(0)
                                        ->addActionLabel(__("Ajouter une nouvelle dépense"))
                                        ->reorderable(false)
                                        ->schema([

                                            Grid::make(3)
                                                ->schema([
                                                    DatePicker::make("date")
                                                        ->required(),

                                                    Select::make("expense_category_id")
                                                        ->label(__("Categorie"))
                                                        ->native(false)
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

                                        ])
                                ]),
                        ])->skippable(true)

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("mission"),
                TextColumn::make("departure")
                    ->label("Date")
                    ->badge()
                    ->date("d M Y"),
                TextColumn::make("total")
                    ->placeholder("-")
                    ->numeric(0, null, '.'),
                TextColumn::make("depenses")
                    ->placeholder("-")
                    ->numeric(0, null, '.'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                $query->select(
                    'voyages.*',
                    DB::raw('(SELECT SUM(bills.total) FROM bills WHERE bills.voyage_id = voyages.id) as total'),
                    DB::raw('(SELECT SUM(expenses.amount) FROM expenses WHERE expenses.voyage_id = voyages.id) as depenses')
                );
            });
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
            'index' => Pages\ListVoyages::route('/'),
            'create' => Pages\CreateVoyage::route('/create'),
            'edit' => Pages\EditVoyage::route('/{record}/edit'),
        ];
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $objects = collect($get('objects'))->filter(fn($item) => !empty ($item['unit_price']) && !empty ($item['quantity']));

        // Calculate subtotal based on the selected products and quantities
        $total = $objects->reduce(function ($total, $objects) {
            return $total + $objects["sous_total"];
        }, 0);

        // Update the state with the new values
        $set('total', $total + $get("commission_fees"));
    }
}
