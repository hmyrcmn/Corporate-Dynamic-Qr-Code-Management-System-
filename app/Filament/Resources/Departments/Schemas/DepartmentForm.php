<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Birim bilgisi')
                ->schema([
                    TextInput::make('name')
                        ->label('Birim adi')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->required(),
                ])
                ->columns(2),
        ]);
    }
}
