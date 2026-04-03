# DynamicQR Laravel

Bu proje, kurumsal birimlerin kendi QR kayitlarini olusturup yonettigi, sabit bir kisa adres uzerinden yonlendirme yaptigi ve tarama analitigi topladigi bir Laravel uygulamasidir.

Bu surumde admin paneli yoktur. Tum yetkili kullanicilar uygulamaya tek giris ekrani olan `/login` uzerinden girer ve sadece kendi birim verilerine erisir.

## 1. Sistem Ne Yapar

Temel akis:

1. Kullanici `/login` ekranindan LDAP veya yerel hesap ile giris yapar.
2. Kullanici kendi birimi icin bir QR kaydi olusturur.
3. Sistem kayda benzersiz bir `short_id` uretir.
4. Kullanici QR gorselini SVG olarak indirir.
5. Son kullanici QR kodu okuttugunda istek once bu uygulamaya gelir.
6. Uygulama taramayi `scan_analytics` tablosuna yazar.
7. Uygulama kullaniciyi hedef URL'ye yonlendirir.

Boylece fiziksel QR kod sabit kalir, hedef URL ise sonradan degistirilebilir.

## 2. Ozellikler

- LDAP veya yerel hesap ile giris
- Birim bazli yetki izolasyonu
- QR kaydi olusturma, guncelleme, silme
- SVG formatinda QR indirme
- Tarama sayisi ve tarama kaydi tutma
- Hedef URL whitelist kontrolu
- LDAP kullanicisindan otomatik birim ve kullanici senkronizasyonu

## 3. Teknoloji Yigini

- PHP 8.3
- Laravel 13
- SQLite
- LdapRecord Laravel
- Endroid QR Code
- Blade
- Vite
- Tailwind CSS
- PHPUnit

## 4. Roller ve Yetki Mantigi

Uygulamada iki rol vardir:

- `DEPT_MANAGER`
- `DEPT_USER`

Bu iki rol su an veri kapsami acisindan ayni davranir: kullanici sadece kendi `department_id` degeri ile eslesen QR kayitlarini gorebilir ve yonetebilir.

Yetki kontrolunun merkezi noktasi `App\Models\QrCode::scopeAccessibleTo()` metodudur. Bu scope:

- kullanicinin birimi varsa sadece o birime ait kayitlari acar
- kullanicinin birimi yoksa hicbir kaydi acmaz

Bu kontrol sadece listelemede degil, su rotalarda da uygulanir:

- `/dashboard`
- `/dashboard/edit/{shortId}`
- `/dashboard/delete/{shortId}`
- `/download-qr/{shortId}`

Sonuc: birim kullanicilari birbirlerinin QR kayitlarini goremez, duzenleyemez, silemez veya indiremez.

## 5. Rotalar

Acik rotalar:

- `GET /`
- `GET /login`
- `POST /login`
- `GET /{shortId}`

Oturum gerektiren rotalar:

- `POST /logout`
- `GET /dashboard`
- `GET /dashboard/create`
- `POST /dashboard/create`
- `GET /dashboard/edit/{shortId}`
- `PUT /dashboard/edit/{shortId}`
- `GET /dashboard/delete/{shortId}`
- `DELETE /dashboard/delete/{shortId}`
- `GET /download-qr/{shortId}`

Not: Bu projede `/admin` veya `/admin/login` rotalari yoktur.

## 6. Giris Sistemi Nasil Calisir

Giris akisi:

1. Kullanici adi normalize edilir.
2. `DOMAIN\\kullanici` veya `kullanici@alanadi` formati `kullanici` haline getirilir.
3. Yerel hesap aktifse once yerel hesap kontrol edilir.
4. LDAP aktifse LDAP bind denenir.
5. LDAP kullanicisi bulunduysa local veritabani ile senkronize edilir.
6. Kullanici pasifse oturum reddedilir.
7. Kullanici icin `department_id` yoksa oturum reddedilir.
8. Basarili giriste oturum acilir ve `last_login_at` guncellenir.

Yerel gelistirme hesabi varsayilan olarak:

- kullanici adi: `operator`
- e-posta: `operator@dynamicqr.local`
- sifre: `ChangeMe123!`

Bu hesap `DatabaseSeeder` ile olusur ve `DEPT_MANAGER` rolunde, birime bagli bir kullanicidir.

## 7. QR Akisi

Kayit olusturma alanlari:

- `title`
- `destination_url`
- `is_active`

Kayit olusturma sirasinda:

1. Veri validate edilir.
2. Hedef URL parse edilir.
3. Host bilgisi `ALLOWED_QR_DOMAINS` whitelist'i ile kontrol edilir.
4. Kayit, oturumdaki kullanicinin birimine bagli olacak sekilde olusturulur.
5. `short_id` eksikse model tarafinda otomatik uretim yapilir.

## 8. Yonlendirme ve Analitik

Public QR rotasi `/{shortId}`:

1. Aktif QR kaydini bulur.
2. Recursive redirect riskini kontrol eder.
3. Tarama verisini `scan_analytics` tablosuna yazar.
4. IP bilgisini dogrudan saklamaz; hash'lenmis deger tutar.
5. Kullaniciyi hedef URL'ye yonlendirir.

Analitik alanlari:

- `timestamp`
- `ip_address_hash`
- `user_agent`
- `country`
- `city`
- `device_type`

## 9. Veritabani Modeli

`users`

- `name`
- `username`
- `email`
- `password`
- `guid`
- `domain`
- `department_id`
- `role`
- `is_active`
- `last_login_at`

`departments`

- `name`
- `is_active`

`qr_codes`

- `short_id`
- `department_id`
- `created_by_id`
- `title`
- `destination_url`
- `is_active`

`scan_analytics`

- `qr_code_id`
- `timestamp`
- `ip_address_hash`
- `user_agent`
- `country`
- `city`
- `device_type`

## 10. Yerelde Calistirma

### Bu makinede kullanilan komutlar

Bu ortamda Laravel komutlari ozel `php.ini` ile calistirildi:

PowerShell:

```powershell
$PHP_BIN = "C:\Users\humeyra.cimen\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
$PHP_INI = "C:\Users\humeyra.cimen\Desktop\yunusEmre\.tools\php.ini"

npm install
npm run build
& $PHP_BIN -c $PHP_INI artisan migrate --seed
& $PHP_BIN -c $PHP_INI -S 127.0.0.1:8012 -t public
```

CMD:

```cmd
cd C:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr
set PHP_BIN=C:\Users\humeyra.cimen\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
set PHP_INI=C:\Users\humeyra.cimen\Desktop\yunusEmre\.tools\php.ini

npm install
npm run build
"%PHP_BIN%" -c "%PHP_INI%" artisan migrate --seed
"%PHP_BIN%" -c "%PHP_INI%" -S 127.0.0.1:8012 -t public
```

Uygulama adresi:

```text
http://127.0.0.1:8012
```

### Standart Laravel kurulumu

PHP ve Composer sisteminizde dogru kuruluysa:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## 11. Gerekli Ortam Degiskenleri

Uygulama:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `APP_TIMEZONE`

Veritabani:

- `DB_CONNECTION`
- `DB_DATABASE`

LDAP:

- `LDAP_ENABLED`
- `LDAP_HOSTS`
- `LDAP_PORT`
- `LDAP_BASE_DN`
- `LDAP_DOMAIN`
- `LDAP_NETBIOS_DOMAIN`
- `LDAP_USERNAME`
- `LDAP_PASSWORD`
- `LDAP_LOGIN_ATTRIBUTE`
- `LDAP_DISPLAY_ATTRIBUTE`
- `LDAP_EMAIL_ATTRIBUTE`
- `LDAP_DEPARTMENT_ATTRIBUTE`
- `LDAP_FORCE_USER_BIND`
- `LDAP_ONLY_ENABLED_USERS`
- `LDAP_USER_FILTER`

Yerel hesap:

- `LOCAL_ACCOUNT_ENABLED`
- `LOCAL_ACCOUNT_USERNAME`
- `LOCAL_ACCOUNT_PASSWORD`

QR guvenligi:

- `ALLOWED_QR_DOMAINS`
- `IP_HASH_SALT`

## 12. Projeyi Sifirdan Ayni Sekilde Kurmak Isterseniz

Bu sistemi sifirdan kurmak icin davranis olarak su parcalar gerekir:

1. `users`, `departments`, `qr_codes`, `scan_analytics` migration'lari
2. `User`, `Department`, `QrCode`, `ScanAnalytics` modelleri
3. LDAP ve yerel hesap destekli auth akisi
4. Birim bazli sorgu scopu
5. QR CRUD controller'lari
6. Public redirect controller'i
7. QR SVG indirme endpoint'i
8. URL whitelist kontrolu
9. Scan analitigi yazimi

Sistemin ayni davranmasi icin su kurallar korunmalidir:

- kullanici adini normalize etmek
- yerel hesabi LDAP'den once denemek
- LDAP kullanicisini local tabloya senkronize etmek
- birimi olmayan kullaniciyi sisteme almamak
- QR kayitlarini departman bazli sinirlamak
- redirect aninda analitik yazmak
- hedef URL'yi whitelist ile sinirlamak
- kisa linki sabit, hedef URL'yi degistirilebilir tutmak

## 13. Testler

Bu repoda calistirilan test komutu:

```powershell
& "C:\Users\humeyra.cimen\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" -c "C:\Users\humeyra.cimen\Desktop\yunusEmre\.tools\php.ini" vendor\bin\phpunit --testdox
```

Test kapsaminda dogrulanan noktalar:

- public sayfalarin acilmasi
- `/admin` ve `/admin/login` rotalarinin olmamasi
- seeded local account ile giris
- domain qualified username ile giris
- seeded local account'in birime bagli olmasi
- kullanicinin sadece kendi birim QR kayitlarini gormesi
- baska birimin QR kaydini indirememe, duzenleyememe ve silememe

## 14. Guvenlik ve Yayina Hazirlik

Kontrol edilen ana alanlar:

- yatay yetki ihlali
- birimler arasi veri sizmasi
- recursive redirect
- URL whitelist atlama denemeleri
- IP spoofing riski

Bu surumde kritik olarak korunmus davranislar:

- kullanici sadece kendi biriminin QR kayitlarini gorebilir
- `download`, `edit` ve `delete` rotalari da ayni scope ile korunur
- scan IP bilgisi hash'lenir
- hedef URL whitelist ile sinirlanir

Yayina cikmadan once mutlaka:

1. `APP_DEBUG=false` yapin.
2. `LOCAL_ACCOUNT_PASSWORD` degerini degistirin.
3. Mümkunse yerel hesabi sadece acil durum senaryosu icin acik tutun.
4. LDAP icin TLS veya LDAPS kullanin.
5. `IP_HASH_SALT` icin guclu bir gizli deger kullanin.
6. `ALLOWED_QR_DOMAINS` listesini minimum gerekli alan adlari ile sinirlayin.
7. Reverse proxy kullaniyorsaniz trusted proxy ayarlarini dogru yapin.

## 15. Onemli Dosyalar

- `routes/web.php`: tum web rotalari
- `app/Http/Controllers/AuthController.php`: login ve logout akisi
- `app/Services/CredentialsAuthenticator.php`: yerel hesap ve LDAP auth orkestrasyonu
- `app/Services/LdapDirectoryAuthenticator.php`: LDAP bind ve local sync
- `app/Http/Controllers/DashboardController.php`: dashboard ve filtreleme
- `app/Http/Controllers/QrCodeController.php`: QR CRUD ve SVG indirme
- `app/Http/Controllers/RedirectController.php`: public yonlendirme ve scan kaydi
- `app/Models/QrCode.php`: `short_id` ve `accessibleTo()` scope'u
- `database/seeders/DatabaseSeeder.php`: varsayilan yerel hesap seed'i

## 16. Hizli Ozet

- Uygulamada admin paneli yoktur.
- Tek giris ekrani `/login` adresidir.
- Varsayilan yerel hesap `operator / ChangeMe123!` olarak seed edilir.
- Kullanicilar sadece kendi birim verilerini gorebilir.
- `/admin` ve `/admin/login` mevcut degildir.
- QR indirme SVG olarak yapilir.
