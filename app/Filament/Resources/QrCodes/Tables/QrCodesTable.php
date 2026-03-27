<?php

namespace App\Filament\Resources\QrCodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QrCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Baslik')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('short_id')
                    ->label('Kisa kod')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('department.name')
                    ->label('Birim')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('creator.username')
                    ->label('Olusturan')
                    ->searchable(),
                TextColumn::make('destination_url')
                    ->label('Hedef URL')
                    ->limit(40)
                    ->tooltip(fn ($record): string => $record->destination_url),
                TextColumn::make('scans_count')
                    ->label('Tarama')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Yayinda')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Olusturma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Birim')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Yayin durumu'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
