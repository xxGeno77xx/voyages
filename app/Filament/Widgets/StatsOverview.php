<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use App\Models\Voyage;
use App\Models\Expense;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;

        $endDate = $this->filters['endDate'] ?? null;

        $voyagesCount = Voyage::when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))->count();

        $gainsTotal = Bill::when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))->sum("total");

        $expensesTotal = Expense::when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
        ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))->sum("amount");

        $rand = [mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100)];

        $totalFraisCommission = Bill::sum('commission_fees');

        $rentabilite = $gainsTotal - ($expensesTotal + $totalFraisCommission) ?? 0;

        $rentabiliteColor = $rentabilite >= 0 ? Color::Green : Color::Red;

        $rentabiliteIcon = $rentabilite >= 0 ? "heroicon-o-arrow-trending-up" : "heroicon-o-arrow-trending-down";

        return [
            Stat::make('Nombre de voyages', $voyagesCount)
                ->icon("heroicon-o-truck")
                ->color(Color::Blue)
                ->chart($rand),

            Stat::make('Recette Totale', number_format($gainsTotal, 0, null, '.'))
                ->icon("heroicon-o-arrow-trending-up")
                ->color(Color::Yellow)
                ->chart($rand),

            Stat::make('Dépense Totale', number_format($expensesTotal, 0, null, '.'))
                ->icon("heroicon-o-arrow-trending-down")
                ->color(Color::Red)
                ->chart($rand),

            Stat::make('Total des frais de commission', number_format($totalFraisCommission, 0, null, '.'))
                ->icon("heroicon-o-arrow-trending-down")
                ->color(Color::Red)
                ->chart($rand),

            Stat::make('Rentabilité Totale', number_format($rentabilite, 0, null, '.'))
                ->icon($rentabiliteIcon)
                ->color($rentabiliteColor)
                ->chart($rand),
        ];
    }
}
