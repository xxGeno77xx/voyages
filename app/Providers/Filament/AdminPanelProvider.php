<?php

namespace App\Providers\Filament;

use Carbon\Carbon;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use App\Models\Voyage;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use EightyNine\Reports\ReportsPlugin;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\UsernameLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentView;
use App\Filament\Resources\CustomEditProfile;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{

    
    public function panel(Panel $panel): Panel
    {

        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER,
                function():string{

                    $startDate = Voyage::first()?->departure ?? today()->format('d M Y');

                    $string = 'Ces chiffres s\'étendent du '. Carbon::parse($startDate)->translatedFormat('d M Y').' à aujourd\'hui';

                    $returnString = is_null($startDate)? " " : $string;

                    return $returnString;

                },scopes: Pages\Dashboard::class,);

        return $panel
            ->default()
            ->sidebarFullyCollapsibleOnDesktop()
            ->id('admin')
            ->path('/')
            ->profile(CustomEditProfile::class)
            ->login(UsernameLogin::class)
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                        ReportsPlugin::make(),
                        // \Phpsa\FilamentAuthentication\FilamentAuthentication::make(),
            ]);
    }
}
