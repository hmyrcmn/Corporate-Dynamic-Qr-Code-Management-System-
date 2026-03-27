<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\QrCode;
use App\Models\ScanAnalytics;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Dinamik QR Genel Gorunum';

    protected ?string $description = 'Birimler, kullanicilar, QR kayitlari ve tarama hareketleri.';

    protected function getStats(): array
    {
        return [
            Stat::make('Birimler', (string) Department::query()->count())
                ->description('Aktif kurumsal birim listesi')
                ->color('cyan'),
            Stat::make('Kullanicilar', (string) User::query()->count())
                ->description('LDAP ve yerel hesaplar')
                ->color('teal'),
            Stat::make('QR Kayitlari', (string) QrCode::query()->count())
                ->description('Tum baglanti kayitlari')
                ->color('sky'),
            Stat::make('Toplam Tarama', (string) ScanAnalytics::query()->count())
                ->description('Yonlendirme hareketleri')
                ->color('emerald'),
        ];
    }
}
