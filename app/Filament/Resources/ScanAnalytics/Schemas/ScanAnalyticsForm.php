<?php

namespace App\Filament\Resources\ScanAnalytics\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScanAnalyticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tarama bilgisi')
                ->schema([
                    Select::make('qr_code_id')
                        ->label('QR kaydi')
                        ->relationship('qrCode', 'title')
                        ->disabled()
                        ->dehydrated(false),
                    DateTimePicker::make('timestamp')
                        ->label('Zaman')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('ip_address_hash')
                        ->label('IP hash')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('user_agent')
                        ->label('User agent')
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(2),
        ]);
    }
}
