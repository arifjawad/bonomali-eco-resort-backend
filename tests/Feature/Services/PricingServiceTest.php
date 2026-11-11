<?php

namespace Tests\Feature\Services;

use App\Models\Rate;
use App\Models\RoomType;
use App\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $pricing;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pricing = app(PricingService::class);
        $this->roomType = RoomType::factory()->create(['base_price' => 7500]);
    }

    public function test_base_price_applies_without_a_rate(): void
    {
        $this->assertSame(7500, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2026-12-01')));
    }

    public function test_seasonal_rate_overrides_base_price_including_its_end_date(): void
    {
        $this->rate('2026-12-20', '2026-12-31', 9000);

        $this->assertSame(9000, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2026-12-20')));
        $this->assertSame(9000, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2026-12-31')));
        $this->assertSame(7500, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2027-01-01')));
    }

    public function test_the_rate_that_starts_latest_wins_when_rates_overlap(): void
    {
        $this->rate('2026-12-01', '2027-01-31', 8000);
        $this->rate('2026-12-24', '2026-12-26', 12000);

        $this->assertSame(8000, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2026-12-23')));
        $this->assertSame(12000, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2026-12-25')));
    }

    public function test_stay_total_sums_each_night_and_excludes_the_check_out_day(): void
    {
        $this->rate('2026-12-20', '2026-12-31', 9000);

        // Nights of 19th (base), 20th and 21st (seasonal); the 22nd is check-out.
        $total = $this->pricing->stayTotal(
            $this->roomType,
            CarbonImmutable::parse('2026-12-19'),
            CarbonImmutable::parse('2026-12-22'),
        );

        $this->assertSame(7500 + 9000 + 9000, $total);
    }

    public function test_rates_of_other_room_types_are_ignored(): void
    {
        Rate::factory()->create(['starts_on' => '2026-12-01', 'ends_on' => '2026-12-31', 'price_per_night' => 1]);

        $this->assertSame(7500, $this->pricing->nightlyPrice($this->roomType, CarbonImmutable::parse('2026-12-10')));
    }

    private function rate(string $startsOn, string $endsOn, int $price): void
    {
        Rate::factory()->for($this->roomType)->create([
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'price_per_night' => $price,
        ]);
    }
}
