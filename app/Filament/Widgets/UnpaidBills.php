<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use Filament\Tables;
use App\Models\Voyage;
use App\Models\Consumer;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Widgets\TableWidget as BaseWidget;

class UnpaidBills extends BaseWidget
{

    protected static ?string $heading = "Factures incomplètes";
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
         ->recordUrl(
            fn (Model $record): string => route('filament.admin.resources.voyages.edit', ['record' => $record]),
        )
            ->query(
               Voyage::query()->join("bills","bills.voyage_id", "voyages.id")
               ->where("remaining_amount", "<>", 0)
                ->select("voyages.*", "bills.remaining_amount", "bills.bill_number" , "bills.sender_id", "bills.receiver_id")
            )
            ->columns([
                TextColumn::make("mission"),

                TextColumn::make("remaining_amount")
                    ->label(__("Reste à payer"))
                    ->badge(),

                TextColumn::make("bill_number")
                ->label(__("Numero de la facture")),

                TextColumn::make("bill_number")
                ->label(__("Numero de la facture")),

                TextColumn::make("sender_id")
                ->label(__("Expéditeur"))
                ->formatStateUsing(fn($state) => Consumer::find($state)->raison_sociale),

                TextColumn::make("receiver_id")
                ->label(__("Destinataire"))
                ->formatStateUsing(fn($state) => Consumer::find($state)->raison_sociale),

            ]);
    }
}
