<?php

namespace App\Providers\Filament;

use App\Filament\Resources\KitchenExpenses\KitchenExpenseResource;
use App\Filament\Resources\KitchenInvoices\KitchenInvoiceResource;
use App\Filament\Resources\KitchenPayments\KitchenPaymentsResource;
use App\Filament\Resources\KitchenSubscriptions\KitchenSubscriptionResource;
use App\Filament\Resources\MealDeliveries\MealDeliveryResource;
use App\Filament\Resources\Meals\MealResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Widgets\AdminLatestPaymentsTable;
use App\Filament\Widgets\KitchenAdminStatsWidget;
use App\Filament\Widgets\KitchenInvoicesSummaryWidget;
use App\Filament\Widgets\KitchenPaymentsPendingTransfersTable;
use App\Filament\Widgets\KitchenPaymentsSummaryWidget;
use App\Filament\Widgets\LatestSubscriptionsTable;
use App\Filament\Widgets\UnpaidInvoicesTable;
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

class KitchenAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kitchen-admin')
            ->path('kitchen-admin')
            ->login()
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->brandName('Kitchen Admin')
            ->colors([
                'primary' => Color::Green,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                        slug: 'my-profile'
                    ),
            ])
            ->resources([
                SubscriberResource::class,
                KitchenSubscriptionResource::class,
                MealResource::class,
                MealDeliveryResource::class,
                KitchenInvoiceResource::class,
                KitchenPaymentsResource::class,
                KitchenExpenseResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                KitchenAdminStatsWidget::class,
                KitchenPaymentsSummaryWidget::class,
                KitchenInvoicesSummaryWidget::class,
                LatestSubscriptionsTable::class,
                AdminLatestPaymentsTable::class,
                UnpaidInvoicesTable::class,
                KitchenPaymentsPendingTransfersTable::class,
                AccountWidget::class,
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
