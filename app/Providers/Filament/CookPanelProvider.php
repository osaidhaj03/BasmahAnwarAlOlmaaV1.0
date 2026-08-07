<?php

namespace App\Providers\Filament;

use App\Filament\Cook\Widgets\CookMealsChart;
use App\Filament\Cook\Widgets\CookPaymentsChart;
use App\Filament\Cook\Widgets\CookPaymentsSummaryWidget;
use App\Filament\Cook\Widgets\CookPendingMealsChart;
use App\Filament\Cook\Widgets\CookStatsOverview;
use App\Filament\Cook\Widgets\CreateKitchenPaymentShortcutWidget;
use App\Filament\Cook\Widgets\LatestPaymentsTable;
use App\Filament\Cook\Widgets\SubscribersWithUnpaidInvoicesTable;
use App\Filament\Resources\KitchenExpenses\KitchenExpenseResource;
use App\Filament\Resources\KitchenInvoices\KitchenInvoiceResource;
use App\Filament\Resources\KitchenPayments\KitchenPaymentsResource;
use App\Filament\Resources\KitchenSubscriptions\KitchenSubscriptionResource;
use App\Filament\Resources\MealDeliveries\MealDeliveryResource;
use App\Filament\Resources\Meals\MealResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Caresome\FilamentNeobrutalism\NeobrutalismeTheme;

class CookPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cook')
            ->path('cook')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->brandName('لوحة التحكم الخاصة بالطباخ')
            ->colors([
                'primary' => Color::Green,
            ])
            ->plugins([
                NeobrutalismeTheme::make(),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                        slug: 'my-profile'
                    ),
            ])
            ->discoverResources(in: app_path('Filament/Cook/Resources'), for: 'App\\Filament\\Cook\\Resources')
            ->resources([
                SubscriberResource::class,
                KitchenSubscriptionResource::class,
                MealResource::class,
                MealDeliveryResource::class,
                KitchenInvoiceResource::class,
                KitchenPaymentsResource::class,
                KitchenExpenseResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Cook/Pages'), for: 'App\\Filament\\Cook\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Cook/Widgets'), for: 'App\\Filament\\Cook\\Widgets')
            ->widgets([
                // AccountWidget::class,
                CookStatsOverview::class,
                CookPaymentsSummaryWidget::class,
                CreateKitchenPaymentShortcutWidget::class,
                CookMealsChart::class,
                CookPendingMealsChart::class,
                CookPaymentsChart::class,
                SubscribersWithUnpaidInvoicesTable::class,
                LatestPaymentsTable::class,
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
            ]);
    }
}
