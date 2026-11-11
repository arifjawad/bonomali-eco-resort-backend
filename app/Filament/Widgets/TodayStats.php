<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\InquiryStatus;
use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Services\AvailabilityService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|array|null $columns = 3;

    protected function getStats(): array
    {
        $today = today();

        return [
            Stat::make('Arrivals today', Booking::notCancelled()->whereDate('check_in', $today)->count())
                ->icon('heroicon-o-arrow-right-end-on-rectangle'),
            Stat::make('Departures today', Booking::notCancelled()->whereDate('check_out', $today)->count())
                ->icon('heroicon-o-arrow-left-start-on-rectangle'),
            Stat::make('In-house now', Booking::where('status', BookingStatus::CheckedIn)->count())
                ->description('Bookings checked in')
                ->icon('heroicon-o-home'),
            Stat::make('Occupancy tonight', app(AvailabilityService::class)->occupancyPercent($today).'%')
                ->description('Active rooms with a guest tonight')
                ->icon('heroicon-o-chart-pie'),
            Stat::make('New inquiries', Inquiry::where('status', InquiryStatus::New)->count())
                ->description('Waiting for a reply')
                ->icon('heroicon-o-inbox-arrow-down')
                ->url(InquiryResource::getUrl('index')),
            Stat::make('Revenue this month', number_format($this->confirmedRevenueThisMonth()).' BDT')
                ->description('Confirmed stays checking in this month')
                ->icon('heroicon-o-banknotes'),
        ];
    }

    /**
     * Confirmed, in-house and finished stays whose check-in falls in this month.
     * Not cash received: payments are tracked later (Phase 4).
     */
    private function confirmedRevenueThisMonth(): int
    {
        return (int) Booking::whereIn('status', BookingStatus::confirmedStatuses())
            ->whereBetween('check_in', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('total_amount');
    }
}
