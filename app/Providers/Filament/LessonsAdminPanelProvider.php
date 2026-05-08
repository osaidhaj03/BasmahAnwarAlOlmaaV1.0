<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Lectures\LectureResource;
use App\Filament\Resources\Lessons\LessonResource;
use App\Filament\Resources\LessonSections\LessonSectionResource;
use App\Filament\Resources\LessonSectionStudents\LessonSectionStudentResource;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Teachers\TeacherResource;
use App\Filament\Widgets\LessonsAdminStatsWidget;
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

class LessonsAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('lessons-admin')
            ->path('lessons-admin')
            ->login()
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->brandName('Lessons Admin')
            ->colors([
                'primary' => Color::Blue,
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
                LessonSectionResource::class,
                LessonSectionStudentResource::class,
                LessonResource::class,
                LectureResource::class,
                AttendanceResource::class,
                StudentResource::class,
                TeacherResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                LessonsAdminStatsWidget::class,
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
