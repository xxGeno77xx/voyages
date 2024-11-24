<?php

namespace App\Filament\Reports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Voyage;
use App\Models\Manager;
use Body\Layout\BodyRow;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use EightyNine\Reports\Report;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Components\Image;
use EightyNine\Reports\Components\Input;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Components\Placeholder;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class VoyageReport extends Report
{
    public ?string $heading = "Report";

    // public ?string $subHeading = "A great report";

    public ?string $startDate = "";

    public ?string $endDate = "";

    public function header(Header $header): Header
    {
        $imagePath = asset('assets/spt.jpg');

         
        return $header
            ->schema([
                Image::make($imagePath),
                // Placeholder::make("test"),

                Text::make("Situation des voyages" )
                    ->title()
                    ->primary() ,

                Text::make("Du ".($this->startDate ?? null). " au " .  ($this->endDate ?? null))
                    ->subtitle(),
                    
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('filter')
                ->label(__('Filtrer'))
                ->icon('heroicon-o-funnel')
                ->submit('filter')
                ->keyBindings(['mod+s']),
        ];
    }


    public function body(Body $body): Body
    {
        return $body
            ->schema([
              

                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make("Registered Users")
                            ->fontXl()
                            ->fontBold()
                            ->primary(),
                        Text::make("This is a list of registered users from the specified date range")
                            ->fontSm()
                            ->secondary(),
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("mission")
                                    ->label("Mission"),
                                Body\TextColumn::make("departure")
                                    ->label("Départ")
                                    ->date("d M Y"),
                                Body\TextColumn::make("total")
                                    ->label("Recette")
                                    ->numeric(0, null, '.')
                                    ->placeholder("-"),
                                    Body\TextColumn::make("depenses")
                                    ->badge()
                                    ->label("Dépenses")
                                    ->numeric(0, null, '.')
                                    ->placeholder("-"),
                            ])
                            ->data(
                                function (?array $filters ) { 
                                   
                                    $this->startDate = $from = Carbon::parse((Str::of($filters['departure'] ?? null)->before("-")->trim()))->format("Y-m-d");
                                    $this->endDate = $to = Carbon::parse((Str::of($filters['departure'] ?? null)->after("-")->trim()))->format("Y-m-d");

                                    return  Voyage::query()->select(
                                        'voyages.*',
                                        DB::raw('(SELECT SUM(bills.total) FROM bills WHERE bills.voyage_id = voyages.id) as total'),
                                        DB::raw('(SELECT SUM(expenses.amount) FROM expenses WHERE expenses.voyage_id = voyages.id) as depenses')
                                    ) ->when($from, function ($query, $date) {
                                        return $query->whereDate('voyages.departure', '>=', $date);
                                    })
                                    ->when($to, function ($query, $date) {
                                        return $query->whereDate('voyages.departure', '<=', $date);
                                    }
                                    )
                                    ->get();

                                    
                                }
                            ),
                        // VerticalSpace::make(),
                        Text::make("Verified Users")
                            ->fontXl()
                            ->fontBold()
                            ->primary(),
                        Text::make("This is a list of verified users from the specified date range")
                            ->fontSm()
                            ->secondary(),
                        
                    ]),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
              
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([

                DateRangePicker::make("departure")
                    ->label("Date")
                    ->placeholder("Sélectionnez une plage de dates"),

            ]);
    }
}
