<?php

namespace App\Services;

use App\Models\Rate;
use App\Models\RoomType;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Room prices in whole BDT.
 *
 * A night costs the room type's base price, unless a seasonal rate covers that
 * night (starts_on..ends_on, both inclusive). If several rates cover it, the one
 * that starts latest wins, so a short "Eid peak" can sit inside a long "Winter season".
 * Extras (extra guests, BBQ, transfers) are not priced here yet.
 */
class PricingService
{
    public function nightlyPrice(RoomType $roomType, CarbonInterface $night): int
    {
        return $this->priceFor($night, $roomType, $this->ratesBetween($roomType, $night, $night));
    }

    /**
     * Total for nights from $checkIn up to (not including) $checkOut.
     */
    public function stayTotal(RoomType $roomType, CarbonInterface $checkIn, CarbonInterface $checkOut): int
    {
        $lastNight = $checkOut->copy()->subDay();

        if ($lastNight->lt($checkIn)) {
            return 0;
        }

        $rates = $this->ratesBetween($roomType, $checkIn, $lastNight);

        $total = 0;
        foreach (CarbonPeriod::create($checkIn, $lastNight) as $night) {
            $total += $this->priceFor($night, $roomType, $rates);
        }

        return $total;
    }

    /**
     * @param  Collection<int, Rate>  $rates  sorted with the latest start first
     */
    private function priceFor(CarbonInterface $night, RoomType $roomType, Collection $rates): int
    {
        $date = $night->toDateString();

        $rate = $rates->first(
            fn (Rate $rate) => $rate->starts_on->toDateString() <= $date && $date <= $rate->ends_on->toDateString()
        );

        return $rate?->price_per_night ?? $roomType->base_price;
    }

    /**
     * @return Collection<int, Rate>
     */
    private function ratesBetween(RoomType $roomType, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $roomType->rates()
            ->whereDate('starts_on', '<=', $to->toDateString())
            ->whereDate('ends_on', '>=', $from->toDateString())
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->get();
    }
}
