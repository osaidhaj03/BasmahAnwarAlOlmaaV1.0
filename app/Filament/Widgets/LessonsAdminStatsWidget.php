<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Lesson;
use App\Models\LessonSection;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LessonsAdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $studentsCount = User::where(function ($query) {
            $query->where('type', 'student')
                ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'student'));
        })->count();

        $teachersCount = User::where(function ($query) {
            $query->where('type', 'teacher')
                ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'teacher'));
        })->count();

        $activeLectures = Lecture::query()
            ->where('status', 'ongoing')
            ->count();

        $presentToday = Attendance::whereDate('attendance_date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $absentToday = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->count();

        return [
            Stat::make('Students', $studentsCount)
                ->description('Registered students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Teachers', $teachersCount)
                ->description('Teaching staff')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Lessons', Lesson::count())
                ->description(LessonSection::count() . ' sections')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('success'),

            Stat::make('Lectures this month', Lecture::whereMonth('lecture_date', $today->month)->whereYear('lecture_date', $today->year)->count())
                ->description($activeLectures . ' active lectures')
                ->descriptionIcon('heroicon-m-presentation-chart-bar')
                ->color('primary'),

            Stat::make('Attendance today', $presentToday)
                ->description($absentToday . ' absent records')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Enrolled students', DB::table('lesson_section_student')->distinct('student_id')->count('student_id'))
                ->description('Unique section enrollments')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),
        ];
    }
}
