<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Mayela · Administration')
            ->colors([
                'primary' => self::navyPalette(),
                'success' => Color::hex('#006C49'),
                'warning' => Color::Amber,
            ])
            ->font('Lexend')
            // Double authentification (application TOTP) obligatoire pour tout le personnel.
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ], isRequired: true)
            ->profile()
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Palette ancrée sur le bleu marine Mayela (#00236F) en nuance 600,
     * celle qu'utilise Filament pour les boutons (Color::hex le placerait en 950).
     */
    private static function navyPalette(): array
    {
        $hex = [
            50 => '#EEF1FB', 100 => '#DCE3F7', 200 => '#B9C6EE', 300 => '#8EA2E2', 400 => '#5F78D2',
            500 => '#2F4FBF', 600 => '#00236F', 700 => '#001D5C', 800 => '#00184B', 900 => '#00133C', 950 => '#000C27',
        ];

        return array_map(fn (string $color) => Color::convertToOklch($color), $hex);
    }
}
