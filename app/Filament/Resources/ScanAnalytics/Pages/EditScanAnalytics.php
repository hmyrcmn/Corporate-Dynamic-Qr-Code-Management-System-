<?php

namespace App\Filament\Resources\ScanAnalytics\Pages;

use App\Filament\Resources\ScanAnalytics\ScanAnalyticsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScanAnalytics extends EditRecord
{
    protected static string $resource = ScanAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
