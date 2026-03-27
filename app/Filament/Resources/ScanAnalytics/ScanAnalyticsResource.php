<?php

namespace App\Filament\Resources\ScanAnalytics;

use App\Filament\Resources\ScanAnalytics\Pages\ListScanAnalytics;
use App\Filament\Resources\ScanAnalytics\Schemas\ScanAnalyticsForm;
use App\Filament\Resources\ScanAnalytics\Tables\ScanAnalyticsTable;
use App\Models\ScanAnalytics;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScanAnalyticsResource extends Resource
{
    protected static ?string $model = ScanAnalytics::class;

    protected static ?string $navigationLabel = 'Tarama Analitigi';

    protected static string|\UnitEnum|null $navigationGroup = 'QR Yonetimi';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return 'Tarama hareketi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tarama hareketleri';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('qrCode');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ScanAnalyticsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScanAnalyticsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScanAnalytics::route('/'),
        ];
    }
}
