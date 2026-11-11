<?php

namespace App\Models;

use App\Enums\RoomCategory;
use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'category', 'vibe_tag', 'tagline', 'description', 'amenities',
    'base_price', 'max_guests', 'image_path', 'is_active', 'sort_order',
])]
class RoomType extends Model
{
    /** @use HasFactory<RoomTypeFactory> */
    use HasFactory;

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    protected function casts(): array
    {
        return [
            'category' => RoomCategory::class,
            'amenities' => 'array',
            'base_price' => 'integer',
            'max_guests' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
