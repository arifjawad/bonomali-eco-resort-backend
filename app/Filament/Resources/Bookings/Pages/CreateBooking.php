<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Exceptions\BookingException;
use App\Filament\Resources\Bookings\BookingResource;
use App\Services\BookingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(BookingService::class)->create($data, auth()->user());
        } catch (BookingException $exception) {
            Notification::make()->danger()->title($exception->getMessage())->persistent()->send();
            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
