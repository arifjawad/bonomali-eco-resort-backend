<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingsPerMonthChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Bookings per month';

    protected ?string $description = 'By check-in month, cancelled excluded. Last 6 months and next 6.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect(range(-6, 5))->map(fn (int $offset) => now()->startOfMonth()->addMonths($offset));

        $countsByMonth = Booking::notCancelled()
            ->whereBetween('check_in', [$months->first()->toDateString(), $months->last()->endOfMonth()->toDateString()])
            ->get(['check_in'])
            ->countBy(fn (Booking $booking) => $booking->check_in->format('Y-m'));

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $months->map(fn ($month) => $countsByMonth->get($month->format('Y-m'), 0))->all(),
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('M Y'))->all(),
        ];
    }
}
