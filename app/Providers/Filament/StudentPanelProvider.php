<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Student\Pages\StudentDashboard;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Caresome\FilamentNeobrutalism\NeobrutalismeTheme;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->login(\App\Filament\Student\Pages\Auth\Login::class)
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->brandName('لوحة التحكم الخاصة بالطالب')
            ->colors([
                'primary' => Color::Yellow,
            ])
            ->plugins([
                NeobrutalismeTheme::make(),
                AuthDesignerPlugin::make()
                    ->login(fn (AuthPageConfig $config) => $config
                        ->media(asset('assets/auth-bg.png'))
                        ->mediaPosition(MediaPosition::Cover)
                        ->blur(0)
                    )
                    ->registration(fn (AuthPageConfig $config) => $config
                        ->media(asset('assets/auth-bg.png'))
                        ->mediaPosition(MediaPosition::Cover)
                        ->blur(2)
                    ),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                        slug: 'my-profile'
                    ),
            ])
            ->pages([
                StudentDashboard::class,
            ])
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\\Filament\\Student\\Resources')
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\\Filament\\Student\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->registration(\App\Filament\Student\Pages\Auth\Register::class)
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
            ->authGuard('web');
    }
}
