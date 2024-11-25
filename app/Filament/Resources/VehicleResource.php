<?php

namespace App\Filament\Resources;

use Closure;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Tables;
use App\Models\Vehicle;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\VehicleType;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VehicleResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VehicleResource\RelationManagers;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $label = 'Véhicules';

    protected static ?string $navigationIcon = 'heroicon-o-truck';
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
                        Grid::make(2)
                            ->schema([
                                TextInput::make("plate_number")
                                    ->label(__("Numéro de plaque"))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->regex('/^(RTG|TG)-\d{4}-[A-Z]{2}$/')
                                    ->placeholder('TG-1234-AB ou RTG-1234')
                                    ->rules([
                                        function ($record) {

                                            return function (string $attribute, $value, Closure $fail) use ($record) {

                                                $existingEngine = Vehicle::where('plate_number', $value)->first();

                                                if ($existingEngine && $record) {
                                                    if ($existingEngine->id != $record->id) {
                                                        $fail('Un engin avec ce numéro de plaque existe déjà.');
                                                    }
                                                } elseif ($existingEngine) {
                                                    $fail('Un engin avec ce numéro de plaque existe déjà.');
                                                }
                                            };
                                        },
                                    ]),
                                Select::make("vehicle_type_id")
                                    ->label(__("Type de véhicule"))
                                    ->options(VehicleType::pluck("label", "id"))
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('label')
                                            ->required()

                                            ->label(__("Libellé")),
                                    ])->createOptionUsing(function (array $data): int {
                                        return VehicleType::create($data)->getKey();
                                    }),
                            ])

                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("plate_number")
                    ->label(__("Numéro de plaque")),

                TextColumn::make("label")
                    ->label(__("Catégorie"))
                    ->badge()
                    ->color(Color::Blue)
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
            ])
            ->modifyQueryUsing(function ($query) {
                $query->join("vehicle_types", "vehicle_types.id", "vehicles.vehicle_type_id")
                    ->select("vehicles.*", "vehicle_types.label");
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
