<?php

namespace App\Support;

use Carbon\Carbon;

class KitchenBillingPeriod
{
    public static function boundsFromMonth(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m-d', "{$month}-25")->startOfDay();
        $end = $start->copy()->addMonthNoOverflow()->startOfDay();

        return [$start, $end];
    }

    public static function currentMonth(): string
    {
        $today = Carbon::today();
        $start = $today->copy()->day(25)->startOfDay();

        if ($today->day < 25) {
            $start->subMonthNoOverflow();
        }

        return $start->format('Y-m');
    }

    public static function options(int $monthsBack = 18, int $monthsForward = 6): array
    {
        $current = Carbon::createFromFormat('Y-m', self::currentMonth())->startOfMonth();
        $first = $current->copy()->subMonthsNoOverflow($monthsBack);
        $last = $current->copy()->addMonthsNoOverflow($monthsForward);
        $options = [];

        for ($month = $last->copy(); $month->greaterThanOrEqualTo($first); $month->subMonthNoOverflow()) {
            [$start, $end] = self::boundsFromMonth($month->format('Y-m'));

            $options[$month->format('Y-m')] = sprintf(
                '%s (%s - %s)',
                $month->format('m/Y'),
                $start->format('d/m/Y'),
                $end->format('d/m/Y')
            );
        }

        return $options;
    }

    public static function label(string $month): string
    {
        [$start, $end] = self::boundsFromMonth($month);

        return sprintf(
            '%s - %s',
            $start->format('d/m/Y'),
            $end->format('d/m/Y')
        );
    }
}
