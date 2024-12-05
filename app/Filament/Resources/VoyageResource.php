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
use App\Enums\EnumStatus;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\ObjectNature;
use App\Models\Conditionning;
use Filament\Resources\Resource;
use App\Models\ExpensesCategorie;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\VehicleResource;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\VoyageResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VoyageResource\RelationManagers;
use App\Filament\Resources\VoyageResource\RelationManagers\BillsRelationManager;
use App\Filament\Resources\VoyageResource\RelationManagers\ExpensesRelationManager;

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
                                    TextInput::make("mission")
                                        ->label(__("Mission"))
                                        ->columnSpanFull()
                                        ->required(),

                                    Grid::make(2)
                                        ->schema([
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
                                        ]),

                                    Grid::make(2)
                                        ->schema([

                                            Section::make("Allée")
                                                ->columns(3)
                                                ->schema([
                                                    DatePicker::make("departure")
                                                        ->label(__("Date de départ"))
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
                                                ]),
                                            Section::make("Retour")
                                                ->columns(3)
                                                ->schema([
                                                    DatePicker::make("arrival")
                                                        ->label(__("Date d'arrivée"))
                                                        ->after("departure"),

                                                    Select::make("arrival_driver_id")
                                                        ->options(Driver::pluck("full_name", "id"))
                                                        ->searchable()
                                                        ->label(__("Chauffeur"))
                                                        ->createOptionForm([
                                                            TextInput::make('full_name')
                                                                ->required()
                                                                ->label(__("Nom complet")),
                                                        ])
                                                        ->createOptionUsing(function (array $data): int {
                                                            return Driver::create($data)->getKey();
                                                        }),
                                                    Select::make("arrival_ass_driver_id")
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
                                                ])

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
                                            ToggleButtons::make('line')
                                                ->label("Aller ou retour")
                                                ->required()
                                                ->inline()
                                                ->options(EnumStatus::class),

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
                                                                                ->live(debounce: 2000)
                                                                                ->afterStateUpdated(function (Get $get, Set $set) {

                                                                                    $set("sous_total", intval($get("unit_price")) * intval($get("quantity")));
// Retrieve all selected products and remove empty rows
                                                                                    $objects = collect($get('../../objects'))->filter(fn($item) => !empty ($item['unit_price']) && !empty ($item['quantity']));
 
                                                                                    // Calculate subtotal based on the selected products and quantities
                                                                                    $total = $objects->reduce(function ($total, $objects) {
                                                                                        return $total + $objects["sous_total"];
                                                                                    }, 0);


                                                                                    $set('../../objects_total', $total);
                                                                                })

                                                                        ]),
                                                                    TextInput::make("sous_total")
                                                                        ->label(__("Prix total"))
                                                                        ->required(),
                                                                ])
                                                        ]),

                                                    Forms\Components\Actions::make([
                                                        Forms\Components\Actions\Action::make('Recalculer le total des objets')
                                                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                                                self::updateTotals($get, $set);

                                                                // $set("remaining_amount", ($get("total") ?? 0) - ($get("paid_amount") ?? 0));
                                                            })->icon('heroicon-m-pencil-square')
                                                            ->iconPosition(IconPosition::After)
                                                            ->extraAttributes([
                                                                'class' => 'mx-auto my-8',
                                                            ])

                                                    ])->columnSpanFull(),

                                                ]),
                                            TextInput::make("objects_total")
                                                ->label((new HtmlString('<i>Total des objets de la facture</i>')))

                                                ->default(0)
                                                ->readOnly()
                                                ->numeric(),

                                            Grid::make(3)
                                                ->schema([
                                                    TextInput::make("total")
                                                        ->label(__("Autres montants"))
                                                        ->required()
                                                        ->default(0)
                                                        ->integer()
                                                        ->live(debounce: 2000)
                                                        ->afterStateUpdated(fn($get, $set) => self::billTotal($get, $set)),

                                                    TextInput::make("paid_amount")
                                                        ->label(__("Montant payé"))
                                                        ->required()
                                                        ->integer()
                                                        ->live(debounce: 2000)
                                                        ->afterStateUpdated(fn($get, $set) => self::billTotal($get, $set)),

                                                    TextInput::make("remaining_amount")
                                                        ->label(__("Reste à payer"))
                                                        ->readOnly()
                                                        ->required()
                                                        ->integer(),
                                                ]),

                                            TextInput::make("observations")
                                                ->label(__("Observations")),

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
                                                        ->suffix("FCFA")
                                                        ->live(debounce:3000)
                                                        ->afterStateUpdated(function($get, $set){
                                                            $year = Carbon::parse($get("date"))->format("Y");

                                                            $managerInitials = Str::upper(substr(Manager::find($get("manager_id"))?->full_name, 0, 3));

                                                            $billsCount = Bill::count();

                                                            $set("bill_number", "N° " . $get("sender_id") . $get("receiver_id") . $year . $managerInitials . $billsCount);
                                                        }),
                                                ])
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

                                            ToggleButtons::make('line')
                                                ->label("Aller ou retour")
                                                ->required()
                                                ->inline()
                                                ->options(EnumStatus::class),

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
                TextColumn::make("mission")
                    ->searchable(),

                TextColumn::make("departure")
                    ->label("Date aller")
                    ->badge()
                    ->color("success")
                    ->date("d M Y"),

                TextColumn::make("arrival")
                    ->label("Date retour")
                    ->badge()
                    ->placeholder("-")
                    ->color("warning")
                    ->date("d M Y"),

                TextColumn::make(__('routing_id'))
                    ->label(__('Trajet'))
                    ->badge()
                    ->color(Color::Blue)
                    ->formatStateUsing(fn($state) => Routing::find($state)->label),

                TextColumn::make("total")
                    ->placeholder("-")
                    ->summarize(Sum::make())
                    ->numeric(0, null, '.'),

                TextColumn::make("depenses")
                    ->placeholder("-")
                    ->label(__("Dépenses"))
                    ->numeric(0, null, '.'),

                TextColumn::make("rentabilité")
                    ->placeholder("0")
                    ->numeric(0, null, '.')
                    ->state(function ($record) {

                        $total = DB::table('bills')->selectRaw('sum(total) as total')
                            ->whereRaw('voyage_id = ?', [$record->id])
                            ->value("total");

                        $depenes = DB::table('expenses')->selectRaw('sum(amount) as expenses')
                            ->whereRaw('voyage_id = ?', [$record->id])
                            ->value("amount");

                        return $total - $depenes;
                    })
                    ->badge()
                    ->Color(fn($state) => $state >= 0 ? Color::Green : Color::Red),

            ])
            ->filters([

                self::rountingFilter(),
                self::dateAllerFilter(),
                self::dateRetourFilter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                $query->select(
                    'voyages.*',
                    DB::raw('(SELECT SUM(bills.total) FROM bills WHERE bills.voyage_id = voyages.id) as total'),
                    DB::raw('(SELECT SUM(expenses.amount) FROM expenses WHERE expenses.voyage_id = voyages.id) as depenses')
                );
            })
            ->deferFilters();
    }

    public static function getRelations(): array
    {
        return [
            BillsRelationManager::class,
            ExpensesRelationManager::class
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

        $set('objects_total', $total /*+ $get("commission_fees")*/);



    }

    public static function rountingFilter()
    {
        return SelectFilter::make('routing_id')
            ->label("Trajet")
            ->multiple()
            ->options(Routing::pluck("label", "id"))
            ->searchable();
    }
    public static function dateAllerFilter()
    {
        return Filter::make('departure')
            ->form([
                Section::make("Date aller")
                    ->collapsed()
                    ->schema([

                        DatePicker::make('created_from')
                            ->label("Date début"),
                        DatePicker::make('created_until')
                            ->label("Date fin"),

                    ])

            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['created_from'],
                        fn(Builder $query, $date): Builder => $query->whereDate('departure', '>=', $date),
                    )
                    ->when(
                        $data['created_until'],
                        fn(Builder $query, $date): Builder => $query->whereDate('departure', '<=', $date),
                    );
            });
    }


    public static function billTotal($get, $set)
    {
        $set("remaining_amount", ($get("total") ?? 0) + ($get("objects_total") ?? 0) - ($get("paid_amount") ?? 0));

    }
    public static function dateRetourFilter()
    {
        return Filter::make('arrival')
            ->form([
                Section::make("Date retour")
                    ->collapsed()
                    ->schema([

                        DatePicker::make('created_from')
                            ->label("Date début"),
                        DatePicker::make('created_until')
                            ->label("Date fin"),

                    ])

            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['created_from'],
                        fn(Builder $query, $date): Builder => $query->whereDate('arrival', '>=', $date),
                    )
                    ->when(
                        $data['created_until'],
                        fn(Builder $query, $date): Builder => $query->whereDate('arrival', '<=', $date),
                    );
            });
    }


}
