<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kullanici bilgisi')
                ->schema([
                    TextInput::make('name')
                        ->label('Ad soyad')
                        ->maxLength(255),
                    TextInput::make('username')
                        ->label('Kullanici adi')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('email')
                        ->label('E-posta')
                        ->email()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Select::make('department_id')
                        ->label('Birim')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('role')
                        ->label('Rol')
                        ->required()
                        ->options([
                            UserRole::SUPER_ADMIN->value => 'Super Admin',
                            UserRole::DEPT_MANAGER->value => 'Birim Yoneticisi',
                            UserRole::DEPT_USER->value => 'Birim Kullanici',
                        ]),
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->required(),
                    TextInput::make('password')
                        ->label('Sifre')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->helperText(config('dynamicqr.ldap_enabled')
                            ? 'LDAP kullaniminda sifre bos birakilir.'
                            : 'Yerel kullanici icin sifre tanimlayin.'),
                    DateTimePicker::make('last_login_at')
                        ->label('Son giris')
                        ->seconds(false)
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(2),
        ]);
    }
}
