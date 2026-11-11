<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Exceptions\BookingException;
use App\Filament\Resources\Bookings\BookingResource;
use App\Services\BookingService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function getSubheading(): ?string
    {
        return "{$this->record->reference} · {$this->record->status->getLabel()}";
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(BookingService::class)->update($record, $data);
        } catch (BookingException $exception) {
            Notification::make()->danger()->title($exception->getMessage())->persistent()->send();
            $this->halt();
        }
    }
}
