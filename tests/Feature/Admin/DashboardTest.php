<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Filament\Widgets\BookingsPerMonthChart;
use App\Filament\Widgets\TodayStats;
use App\Filament\Widgets\UpcomingArrivals;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-11-12 10:00');
        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_loads_with_data(): void
    {
        Booking::factory()->create(['check_in' => today(), 'check_out' => today()->addDays(2)]);

        $this->get('/admin')->assertOk();
    }

    public function test_stats_show_todays_figures(): void
    {
        $room = Room::factory()->create();
        Room::factory()->create(); // 2 active rooms

        Booking::factory()->for($room)->status(BookingStatus::CheckedIn)->create([
            'check_in' => '2026-11-11', 'check_out' => '2026-11-13', 'total_amount' => 15000,
        ]);
        Booking::factory()->status(BookingStatus::Cancelled)->create([
            'check_in' => '2026-11-12', 'check_out' => '2026-11-13', 'total_amount' => 99000,
        ]);
        Inquiry::factory()->count(2)->create();

        Livewire::test(TodayStats::class)
            ->assertSee('Occupancy tonight')
            ->assertSee('33%') // 1 of 3 active rooms (the cancelled booking's room counts too)
            ->assertSee('15,000 BDT')
            ->assertDontSee('99,000');
    }

    public function test_upcoming_arrivals_lists_the_next_seven_days_only(): void
    {
        $soon = Booking::factory()->create(['check_in' => '2026-11-14', 'check_out' => '2026-11-15']);
        $later = Booking::factory()->create(['check_in' => '2026-11-25', 'check_out' => '2026-11-26']);

        Livewire::test(UpcomingArrivals::class)
            ->assertCanSeeTableRecords([$soon])
            ->assertCanNotSeeTableRecords([$later]);
    }

    public function test_chart_renders(): void
    {
        Booking::factory()->create(['check_in' => '2026-11-20', 'check_out' => '2026-11-21']);

        Livewire::test(BookingsPerMonthChart::class)->assertOk();
    }
}
