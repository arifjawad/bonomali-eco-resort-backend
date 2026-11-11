<?php

namespace App\Filament\Resources\Rates\Pages;

use App\Filament\Resources\Rates\RateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRates extends ManageRecords
{
    protected static string $resource = RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
