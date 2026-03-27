<?php

namespace App\Filament\Resources\ScanAnalytics\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScanAnalyticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('timestamp', 'desc')
            ->columns([
                TextColumn::make('qrCode.title')
                    ->label('QR kaydi')
                    ->searchable(),
                TextColumn::make('timestamp')
                    ->label('Zaman')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('country')
                    ->label('Ulke')
                    ->placeholder('-'),
                TextColumn::make('city')
                    ->label('Sehir')
                    ->placeholder('-'),
                TextColumn::make('device_type')
                    ->label('Cihaz')
                    ->placeholder('-'),
                TextColumn::make('user_agent')
                    ->label('User agent')
                    ->limit(42)
                    ->tooltip(fn ($record): string => $record->user_agent ?? ''),
                TextColumn::make('ip_address_hash')
                    ->label('IP hash')
                    ->limit(18),
            ])
            ->filters([
                SelectFilter::make('qr_code_id')
                    ->label('QR kaydi')
                    ->relationship('qrCode', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
