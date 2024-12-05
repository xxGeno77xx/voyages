<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Voyage;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class VoyagesChart extends ChartWidget
{
    protected static ?string $heading = 'Nombre de voyages';
    public ?string $filter = 'today';

    protected function getFilters(): ?array
{
    return [
        'today' =>  'Aujourd\'hui',
        'week' =>   'Semaine dernière',
        'month' =>  'Mois courant',
        'year' =>   'Année courante',
    ];
}

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        

        match($activeFilter){
            'today' =>  $data = Trend::model(Voyage::class)->between(start: now(), end: now(),)->perDay()->count(),
            'week' =>   $data = Trend::model(Voyage::class)->between(start: now()->startOfWeek(), end: now()->endOfWeek(),)->perDay()->count(),
            'month' => $data = Trend::model(Voyage::class)->between(start: now()->startOfMonth(), end: now()->endOfMonth(),)->perDay()->count(),
            'year' =>  $data = Trend::model(Voyage::class)->between(start: now()->startOfYear(), end: now()->endOfYear(),)->perMonth()->count(),
        };

        match($activeFilter){
            'today' =>  $dateformat = "l",
            'week' =>   $dateformat = "l d",
            'month' => $dateformat = "l d",
            'year' =>  $dateformat = "M",
        };
        
        return [
            'datasets' => [
                [
                    'label' => 'Nombre de voyages',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->translatedFormat($dateformat)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
