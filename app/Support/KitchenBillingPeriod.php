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

    public static function boundsFromFilterData(?array $data): array
    {
        if (! empty($data['month_number']) && ! empty($data['year'])) {
            return self::boundsFromMonth(sprintf('%04d-%02d', (int) $data['year'], (int) $data['month_number']));
        }

        if (! empty($data['month']) && is_string($data['month'])) {
            return self::boundsFromMonth($data['month']);
        }

        if (! empty($data['from']) || ! empty($data['to'])) {
            $start = ! empty($data['from'])
                ? Carbon::parse($data['from'])->startOfDay()
                : Carbon::parse($data['to'])->startOfDay()->subMonthNoOverflow();

            $end = ! empty($data['to'])
                ? Carbon::parse($data['to'])->startOfDay()
                : $start->copy()->addMonthNoOverflow();

            return [$start, $end];
        }

        return self::boundsFromMonth(self::currentMonth());
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

    public static function monthOptions(): array
    {
        return [
            1 => '01 - January',
            2 => '02 - February',
            3 => '03 - March',
            4 => '04 - April',
            5 => '05 - May',
            6 => '06 - June',
            7 => '07 - July',
            8 => '08 - August',
            9 => '09 - September',
            10 => '10 - October',
            11 => '11 - November',
            12 => '12 - December',
        ];
    }

    public static function yearOptions(int $yearsBack = 3, int $yearsForward = 1): array
    {
        $currentYear = (int) Carbon::today()->year;
        $years = range($currentYear - $yearsBack, $currentYear + $yearsForward);

        return array_combine($years, $years);
    }

    public static function currentMonthNumber(): int
    {
        return (int) Carbon::createFromFormat('Y-m', self::currentMonth())->month;
    }

    public static function currentYear(): int
    {
        return (int) Carbon::createFromFormat('Y-m', self::currentMonth())->year;
    }

    public static function label(string $month): string
    {
        [$start, $end] = self::boundsFromMonth($month);

        return self::labelFromBounds($start, $end);
    }

    public static function labelFromFilterData(?array $data): string
    {
        [$start, $end] = self::boundsFromFilterData($data);

        return self::labelFromBounds($start, $end);
    }

    public static function labelFromBounds(Carbon $start, Carbon $end): string
    {
        return sprintf(
            '%s - %s',
            $start->format('d/m/Y'),
            $end->format('d/m/Y')
        );
    }
}
