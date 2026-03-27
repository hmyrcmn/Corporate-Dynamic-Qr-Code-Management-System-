<?php

namespace App\Filament\Resources\ScanAnalytics\Pages;

use App\Filament\Resources\ScanAnalytics\ScanAnalyticsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScanAnalytics extends ListRecords
{
    protected static string $resource = ScanAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
