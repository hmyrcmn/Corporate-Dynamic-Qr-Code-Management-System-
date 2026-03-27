<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('username')
            ->columns([
                TextColumn::make('name')
                    ->label('Ad soyad')
                    ->searchable(),
                TextColumn::make('username')
                    ->label('Kullanici adi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Birim')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SUPER_ADMIN' => 'danger',
                        'DEPT_MANAGER' => 'warning',
                        default => 'info',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('last_login_at')
                    ->label('Son giris')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Birim')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'SUPER_ADMIN' => 'Super Admin',
                        'DEPT_MANAGER' => 'Birim Yoneticisi',
                        'DEPT_USER' => 'Birim Kullanici',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Aktif durumu'),
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
