<?php

namespace App\Filament\Resources\QrCodes\Schemas;

use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QrCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Baglanti bilgisi')
                ->schema([
                    TextInput::make('title')
                        ->label('Baslik')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('short_id')
                        ->label('Kisa kod')
                        ->maxLength(12)
                        ->unique(ignoreRecord: true)
                        ->helperText('Bos birakildiginda sistem otomatik olusturur.'),
                    Textarea::make('destination_url')
                        ->label('Hedef URL')
                        ->rows(4)
                        ->required()
                        ->rule('url')
                        ->rule(function (): Closure {
                            return function (string $attribute, mixed $value, Closure $fail): void {
                                $host = (string) parse_url((string) $value, PHP_URL_HOST);
                                $allowedDomains = collect(config('dynamicqr.allowed_qr_domains'));

                                $isAllowed = $allowedDomains->isEmpty() || $allowedDomains->contains(
                                    fn (string $domain): bool => $host === $domain || str_ends_with($host, '.'.$domain),
                                );

                                if (! $isAllowed) {
                                    $fail('Yalnizca izinli kurumsal alan adlarina yonlendirme yapabilirsiniz.');
                                }
                            };
                        })
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Yonetim ayarlari')
                ->schema([
                    Select::make('department_id')
                        ->label('Birim')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('created_by_id')
                        ->label('Olusturan kullanici')
                        ->options(fn (): array => User::query()->orderBy('username')->pluck('username', 'id')->all())
                        ->searchable()
                        ->default(fn (): ?int => auth()->id()),
                    Toggle::make('is_active')
                        ->label('Yayinda')
                        ->default(true)
                        ->required(),
                ])
                ->columns(2),
        ]);
    }
}
