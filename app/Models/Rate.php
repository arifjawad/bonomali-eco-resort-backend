<?php

namespace App\Models;

use Database\Factories\RateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['room_type_id', 'name', 'starts_on', 'ends_on', 'price_per_night'])]
class Rate extends Model
{
    /** @use HasFactory<RateFactory> */
    use HasFactory;

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'price_per_night' => 'integer',
        ];
    }
}
