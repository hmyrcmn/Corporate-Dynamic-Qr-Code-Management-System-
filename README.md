# DynamicQR Laravel

Bu proje, kurumsal birimlerin dinamik QR baglantilari uretmesi, bu baglantilari daha sonra ayni kisa adresi bozmadan guncellemesi ve tarama hareketlerini izlemesi icin gelistirilmis bir Laravel uygulamasidir.

Uygulama iki ana yuzeyden olusur:

- Kullanici paneli: LDAP veya yerel hesap ile giris yapan personelin kendi birimi icin QR kaydi yonetmesi
- Filament admin paneli: yalnizca aktif `SUPER_ADMIN` kullanicilarin tum sistem verisini yonetmesi

Bu README, projeyi hic bilmeyen birinin ayni davranisi tekrar kurabilecek kadar ayrintili olacak sekilde hazirlanmistir.

## 1. Projenin Tam Olarak Ne Yaptigi

Sistemin temel amaci kisa bir QR adresi uretmektir.

Ornek akis:

1. Kullanici sisteme giris yapar.
2. Bir baslik ve hedef URL girer.
3. Sistem bu kayit icin benzersiz bir `short_id` uretir.
4. QR gorseli, bu kisa adrese yonlenecek sekilde PNG olarak hazirlanir.
5. Son kullanici QR kodu okuttugunda sistem once kendi kisa adresine gelir.
6. Sistem taramayi `scan_analytics` tablosuna kaydeder.
7. Sistem kullaniciyi asil hedef URL'ye yonlendirir.

Boylece QR baskisi sabit kalir, ama hedef URL sonradan degistirilebilir.

## 2. One Cikan Ozellikler

- LDAP tabanli kimlik dogrulama destegi
- LDAP kapaliyken veya acil durum senaryosu icin yerel super admin hesabi
- Departman bazli veri erisimi
- Dinamik kisa link uretimi
- PNG formatinda QR kod indirme ve inline onizleme
- Tarama sayisi takibi
- Filtrelenebilir kullanici dashboard'u
- Filament ile ayri yonetim paneli
- LDAP kullanici bilgisinden otomatik kullanici ve departman esleme
- Yalnizca izin verilen alan adlarina QR yonlendirmesi

## 3. Kullanilan Teknolojiler

### Backend

- PHP `^8.3`
- Laravel `^13.0`
- Eloquent ORM
- Laravel session, cache ve queue altyapisi
- SQLite varsayilan gelistirme veritabani
- LDAP entegrasyonu icin `directorytree/ldaprecord-laravel ^3.4`
- QR uretimi icin `endroid/qr-code ^6.0`
- Admin panel icin `filament/filament ^5.4`

### Frontend

- Blade template yapisi
- Vite `^8`
- Tailwind CSS `^4`
- Az miktarda vanilla JavaScript
- Tamamen sunucu tarafli sayfa mimarisi, SPA yok

### Test ve Gelistirme

- PHPUnit `^12.5`
- Laravel test runner
- Laravel Pint
- Laravel Pail

## 4. Mimari Ozet

Sistemin ana parcalari:

- `routes/web.php`: kullaniciya acik web rotalari
- `app/Http/Controllers/*`: giris, dashboard, QR CRUD ve yonlendirme akislar
- `app/Services/LdapDirectoryAuthenticator.php`: ozel LDAP dogrulama ve kullanici senkronizasyonu
- `app/Models/*`: veritabani modelleri
- `resources/views/*`: Blade arayuzleri
- `app/Providers/Filament/AdminPanelProvider.php`: Filament panel konfigrasyonu
- `app/Filament/*`: admin panel kaynaklari

Yuksek seviye mimari:

1. Kullanici `/login` uzerinden giris dener.
2. Sistem once yerel admin hesabini dener.
3. Yerel giris olmazsa LDAP yapilandirmasina gore LDAP dogrulamasi yapilir.
4. Giris basariliysa kullanici lokal veritabaninda senkronize edilir.
5. Kullanici `/dashboard` icinde yalnizca yetkili oldugu QR kayitlarini gorur.
6. QR indirildiginde sistem kisa link adresini encode eden PNG uretir.
7. Dis kullanici QR'i okuttugunda `/{shortId}` rotasi devreye girer ve yonlendirme yapilir.

## 5. Kimlik Dogrulama ve Yetkilendirme Nasil Calisiyor

### 5.1 Yerel giris

LDAP kapaliysa veya yerel acil durum hesabi aktifse sistem once lokal kullanici tablosunda giris dener.

Yerel kullanici girisi icin kullanilan alanlar:

- `username`
- `password`
- `is_active = true`

Varsayilan ornek degerler `.env.example` icinde:

- `LOCAL_SUPER_ADMIN_ENABLED=true`
- `LOCAL_SUPER_ADMIN_USERNAME=admin`
- `LOCAL_SUPER_ADMIN_PASSWORD=ChangeMe123!`

`DatabaseSeeder`, bu hesabi ilk kurulumda olusturur.

### 5.2 LDAP giris

LDAP aktifse sistem iki moddan birini kullanir:

- Servis hesabi ile LDAP provider uzerinden `Auth::attempt`
- Dogrudan son kullanici bind akisi

Dogrudan bind icin aday kullanici adlari su sirayla denenir:

- Kullanicinin yazdigi deger
- normalize edilmis kullanici adi
- `username@domain`
- `NETBIOS\username`

Kullanici basariyla bind olursa dizinden su alanlar okunur:

- kullanici adi
- ad soyad
- e-posta
- departman
- aktif/pasif durumu

Bu bilgiler local `users` ve gerekirse `departments` tablosuna senkronize edilir.

### 5.3 Kullanici adi normalize etme

`App\Support\LdapUsername` su donusumleri yapar:

- bastaki ve sondaki bosluklari temizler
- `DOMAIN\kullanici` yazildiysa yalnizca kullanici kismini alir
- `kullanici@alanadi` yazildiysa yalnizca kullanici kismini alir
- sonucu kucuk harfe cevirir

Bu sayede su girisler ayni kullaniciya duser:

- `admin`
- `YEE\admin`
- `admin@yee.org.tr`

### 5.4 Roller

Tanimli roller:

- `SUPER_ADMIN`
- `DEPT_MANAGER`
- `DEPT_USER`

Mevcut yetki davranisi:

- `SUPER_ADMIN`: tum QR kayitlarina erisebilir, Filament admin paneline girebilir
- `DEPT_MANAGER`: dashboard tarafinda bugun icin `DEPT_USER` ile ayni gorunur davranisa sahip
- `DEPT_USER`: yalnizca kendi departmaninin QR kayitlarini gorur ve yonetir

### 5.5 Giris sonrasi guvenlik kontrolleri

Basarili giristen sonra sistem sunlari kontrol eder:

- kullanici aktif mi
- global erisimi yoksa `department_id` dolu mu

Aktif olmayan veya departmansiz normal kullanici sisteme alinmaz.

## 6. QR Yonetimi Nasil Calisiyor

### 6.1 Kayit olusturma

Kullanici panelindeki QR olusturma formu su alanlari alir:

- `title`
- `destination_url`
- `is_active`

Sistem olusturma sirasinda:

1. kullanicinin departmanini bulur
2. form verisini validate eder
3. hedef URL'nin izinli domain listesinde olup olmadigini kontrol eder
4. kayit olusturur
5. `short_id` yoksa model icinde otomatik uretir

### 6.2 `short_id` uretimi

`App\Models\QrCode` modelinin `creating` hook'u:

- 6 karakterlik rastgele bir kucuk harf/rakam kombinasyonu uretir
- cakisma varsa yeniden uretir

### 6.3 Hedef URL guvenligi

Sistem her URL'yi kabul etmez.

Izin verilen alan adlari `ALLOWED_QR_DOMAINS` ile belirlenir.

Varsayilan ornek:

- `yee.org.tr`
- `gov.tr`
- `youtube.com`

Kontrol mantigi:

- tam host eslesmesi kabul edilir
- alt alan adlari da kabul edilir

Ornek:

- `https://yee.org.tr/abc` kabul edilir
- `https://sub.yee.org.tr/abc` kabul edilir
- `https://example.com` reddedilir

### 6.4 QR indirme

`/download-qr/{shortId}` rotasi:

- kaydin erisilebilir oldugunu dogrular
- QR PNG uretir
- veri olarak kisa yonlendirme adresini encode eder
- `inline=1` verilirse tarayicida onizlenebilir
- aksi halde dosya olarak indirilir

### 6.5 Yonlendirme ve analitik

`/{shortId}` rotasi herkese aciktir.

Yonlendirme aninda sistem:

1. ilgili aktif QR kaydini bulur
2. recursive redirect olup olmadigini kontrol eder
3. IP bilgisini dogrudan degil hash'lenmis olarak kaydeder
4. user-agent bilgisini kaydeder
5. hedef URL'ye `redirect()->away(...)` ile yonlendirir

Analitik tablosunda su alanlar bulunur:

- `qr_code_id`
- `timestamp`
- `ip_address_hash`
- `user_agent`
- `country`
- `city`
- `device_type`

Not: Su an kod tarafinda `country`, `city` ve `device_type` doldurulmuyor; kolonlar ileri kullanim icin hazir.

## 7. Admin Paneli Nasil Calisiyor

Admin panel Filament ile `/admin` altinda acilir.

Giris kurali:

- yalnizca `is_active = true` ve `role = SUPER_ADMIN` kullanicilar girebilir

Admin panelde yonetilen kaynaklar:

- Birimler
- Kullanicilar
- QR kayitlari
- Tarama analitigi

Onemli davranis:

- LDAP aktifken Filament uzerinden manuel kullanici olusturma kapatilmistir
- kullanicilar, LDAP senkronizasyonu ile veya mevcut yerel kayitlarla yonetilir

## 8. Veritabani Semasi

### `users`

Temel alanlar:

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

Iliskiler:

- `belongsTo Department`
- `hasMany QrCode` (`created_by_id`)

### `departments`

Alanlar:

- `name`
- `is_active`

Iliskiler:

- `hasMany User`
- `hasMany QrCode`

### `qr_codes`

Alanlar:

- `short_id`
- `department_id`
- `created_by_id`
- `title`
- `destination_url`
- `is_active`

Iliskiler:

- `belongsTo Department`
- `belongsTo User` (`created_by_id`)
- `hasMany ScanAnalytics`

### `scan_analytics`

Alanlar:

- `qr_code_id`
- `timestamp`
- `ip_address_hash`
- `user_agent`
- `country`
- `city`
- `device_type`

## 9. Rota Haritasi

### Acik rotalar

- `GET /` : landing sayfasi
- `GET /login` : giris formu
- `POST /login` : giris denemesi
- `GET /{shortId}` : kisa link yonlendirme

### Giris isteyen rotalar

- `POST /logout`
- `GET /dashboard`
- `GET /dashboard/create`
- `POST /dashboard/create`
- `GET /dashboard/edit/{shortId}`
- `PUT /dashboard/edit/{shortId}`
- `GET /dashboard/delete/{shortId}`
- `DELETE /dashboard/delete/{shortId}`
- `GET /download-qr/{shortId}`

### Filament

- `GET /admin`
- `GET /admin/login`

Onemli not:

`/{shortId}` catch-all rotasi oldugu icin tek segmentli tum bilinmeyen adresler QR kisa kodu gibi ele alinir.

## 10. Arayuz Katmani

Arayuz tamamen Blade ile olusturulmustur.

Sayfalar:

- `resources/views/landing.blade.php`: pazarlama/karsilama ekrani
- `resources/views/auth/login.blade.php`: giris ekrani
- `resources/views/dashboard/index.blade.php`: QR listeleme ve filtre ekrani
- `resources/views/qr/form.blade.php`: olusturma/duzenleme formu
- `resources/views/qr/delete.blade.php`: silme onayi
- `resources/views/errors/404.blade.php`: ozel 404 ekrani

Frontend davranislari:

- tema secimi `localStorage` icinde saklanir
- mobilde sidebar ac/kapat davranisi vardir
- landing sayfasinda akis kartina kaydirma animasyonu vardir
- dashboard'da modal uzerinden QR onizleme yapilir

## 11. Projeyi Sifirdan Kurma

Bu bolum, ayni sistemi yeniden kurmak icin en net baslangic noktasidir.

### 11.1 Gerekenler

- PHP 8.3+
- Composer
- Node.js 20+
- npm
- SQLite veya baska destekli veritabani

### 11.2 Gerekli PHP eklentileri

Bu proje icin en az su eklentiler aktif olmalidir:

- `mbstring`
- `dom`
- `xml`
- `xmlwriter`
- `json`
- `tokenizer`
- `pdo_sqlite` veya kullandiginiz DB surucusu

Bu kontrol kritik: mevcut ortamda `mbstring` eksik oldugu icin `php artisan` komutlari ve testler calismadi.

### 11.3 Kurulum adimlari

1. Bagimliliklari yukleyin.

```bash
composer install
npm install
```

2. Ortam dosyasini hazirlayin.

```bash
copy .env.example .env
```

3. Uygulama anahtarini uretin.

```bash
php artisan key:generate
```

4. Gelistirme icin SQLite kullanacaksaniz `database/database.sqlite` dosyasinin var oldugundan emin olun.

5. Veritabanini migrate edin ve seed calistirin.

```bash
php artisan migrate --seed
```

6. Frontend derlemesini alin.

```bash
npm run build
```

7. Uygulamayi baslatin.

```bash
php artisan serve
```

Isterseniz gelistirme sirasinda:

```bash
npm run dev
```

### 11.4 Ilk giris

LDAP kapaliysa varsayilan ornek giris:

- kullanici adi: `admin`
- sifre: `ChangeMe123!`

Bu degerler uretim ortaminda mutlaka degistirilmelidir.

## 12. `.env` Yapilandirma Rehberi

### 12.1 Uygulama

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `APP_TIMEZONE`

### 12.2 Veritabani

Varsayilan ornek SQLite'dir:

- `DB_CONNECTION=sqlite`

MySQL kullanacaksaniz:

- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### 12.3 Session, cache, queue

Varsayilan ornekler:

- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

Bu durumda ilgili migration'larin calismis olmasi gerekir.

### 12.4 LDAP

Temel LDAP degiskenleri:

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
- `LDAP_SSL`
- `LDAP_TLS`
- `LDAP_FORCE_USER_BIND`
- `LDAP_ONLY_ENABLED_USERS`
- `LDAP_USER_FILTER`
- `LDAP_SUPER_ADMIN_USERNAME`

LDAP senaryosu ornegi:

- `LDAP_ENABLED=true`
- `LDAP_HOSTS=ldap-sunucu-1,ldap-sunucu-2`
- `LDAP_BASE_DN=DC=yee,DC=org,DC=tr`
- `LDAP_DOMAIN=yee.org.tr`
- `LDAP_NETBIOS_DOMAIN=YEE`

### 12.5 Yerel acil durum hesabi

- `LOCAL_SUPER_ADMIN_ENABLED`
- `LOCAL_SUPER_ADMIN_USERNAME`
- `LOCAL_SUPER_ADMIN_PASSWORD`

Onemli davranis:

`LDAP_SUPER_ADMIN_USERNAME` doluysa ve bu kullanici yerel admin ile ayni degilse, seed edilen yerel hesap `SUPER_ADMIN` degil `DEPT_MANAGER` olur. Yani gercek global yetki LDAP tarafina birakilir.

### 12.6 QR guvenligi

- `ALLOWED_QR_DOMAINS`
- `IP_HASH_SALT`

## 13. LDAP Akisini Adim Adim Anlama

LDAP acikken girisin teknik sirasi soyledir:

1. Kullanici formdan kullanici adi ve sifre girer.
2. Kullanici adi normalize edilir.
3. Uygulama once yerel acil durum hesabini dener.
4. Yerel giris olmazsa LDAP akisina gecilir.
5. Base DN bossa islem durur.
6. Bind icin farkli kullanici adi varyasyonlari denenir.
7. Kullanici bind olursa dizinden profil cekilir.
8. Gerekirse `departments` kaydi olusturulur.
9. Kullanici local tabloda bulunur veya olusturulur.
10. `LDAP_SUPER_ADMIN_USERNAME` eslesiyorsa rol `SUPER_ADMIN`, aksi halde `DEPT_USER` atanir.
11. Kullanici aktif degilse veya departmansiz normal kullaniciysa giris geri cevrilir.
12. Oturum yenilenir ve `last_login_at` guncellenir.

LDAP kullanici dogrulama testi icin ek komut:

```bash
php artisan dynamicqr:ldap:lookup kullaniciadi
```

## 14. Dashboard Davranisi

Dashboard, kullanicinin erisebildigi QR kayitlarini listeler.

Hesaplanan ozetler:

- toplam gorulebilen QR sayisi
- aktif QR sayisi
- toplam tarama sayisi
- filtrelenmis kayit sayisi

Desteklenen filtreler:

- yalnizca aktif kayitlar
- yalnizca taranmis kayitlar
- her iki filtrenin birlikte kullanimi

Siralama:

- taranmis filtresi acikken `scans_count` oncelikli
- diger durumda `created_at` azalan

## 15. Filament Kaynaklari

### Birimler

- birim adi
- aktiflik
- kullanici sayisi
- QR sayisi

### Kullanicilar

- ad soyad
- kullanici adi
- e-posta
- birim
- rol
- aktiflik
- son giris zamani

### QR kayitlari

- kisa kod
- hedef URL
- bagli departman
- olusturan kullanici
- tarama sayisi

### Tarama analitigi

- zaman
- QR kaydi
- IP hash
- user-agent

## 16. Dizin Yapisi

```text
app/
  Enums/
  Filament/
  Http/Controllers/
  Ldap/
  Models/
  Providers/
  Services/
  Support/
bootstrap/
config/
database/
  migrations/
  seeders/
public/
resources/
  css/
  js/
  views/
routes/
tests/
```

## 17. Mevcut Test Kapsami

Projede ozellikle su senaryolar icin feature test var:

- public sayfalar aciliyor mu
- guest dashboard'dan login'e yonleniyor mu
- seeded local admin giris yapabiliyor mu
- `YEE\admin` formati normalize oluyor mu
- LDAP super admin konfigure edilirse seed rolu degisiyor mu
- departman kullanicisi sadece kendi departman QR'larini goruyor mu

Henuz test kapsami olmayan ama kritik alanlar:

- QR olusturma/guncelleme/silme
- domain whitelist validasyonu
- redirect analitik kaydi
- Filament yetki sinirlari
- LDAP servis hesabi ve direkt bind varyasyonlari

## 18. Dogrulanan Bulgular ve Hatalar

Bu bolum, kod ve komut calistirmalariyla dogrulanmis mevcut sorunlari icerir.

### Kritik

1. `mbstring` eksikse uygulama ayaga kalkmiyor.
   - Bu ortamda `php artisan about` cagrisi `Illuminate\Support\Str::studly()` icinde `mb_split` eksikligiyle dustu.
   - `php artisan test` de ayni nedenle baslayamadi.
   - Sonuc: sunucu komutlari, testler ve bazi runtime akislar calismaz.

2. Departmansiz global kullanici dashboard'dan QR olusturamiyor.
   - Giris akisi global kullaniciyi departman olmadan kabul ediyor.
   - Dashboard menusu her giris yapan kullaniciya `Yeni Kayit` baglantisini gosteriyor.
   - Ancak `QrCodeController::store()` kullanici departmani yoksa `400 Bad Request` ile islemi kesiyor.
   - Sonuc: LDAP ile gelen bir `SUPER_ADMIN`, departman bilgisi yoksa forma girebilir ama kaydi tamamlayamaz.

### Orta

3. `run-local.ps1` tasinabilir degil.
   - PHP yolu tek bir Windows kullanici profiline sabitlenmis.
   - `php.ini` yolu da proje disindaki ozel bir `.tools` klasorune bagli.
   - Sonuc: script baska makinede buyuk olasilikla dogrudan calismaz.

4. Ornek ortam dosyasi uretim oncesi mutlaka temizlenmeli.
   - `.env.example` icinde somut LDAP host ornegi bulunuyor.
   - Ayni dosyada varsayilan yerel admin sifresi de acikca yer aliyor.
   - Sonuc: bu degerler paylasilmis repo icinde birakilirsa operasyonel risk olusturur.

### Dogrulama ozeti

- `npm run build` basariyla gecti
- `php artisan about` basarisiz: `mbstring` eksik
- `php artisan test` basarisiz: `mbstring` eksik

## 19. Uretime Alma Notlari

Uretime cikmadan once en az sunlari yapin:

1. `APP_DEBUG=false`
2. gercek `APP_URL` tanimlayin
3. `LOCAL_SUPER_ADMIN_PASSWORD` degistirin
4. gerekiyorsa `LOCAL_SUPER_ADMIN_ENABLED=false` yapin
5. `IP_HASH_SALT` degerini guclu ve gizli bir degerle degistirin
6. `ALLOWED_QR_DOMAINS` listesini kurumsal gereksinime gore daraltin
7. PHP eklentilerini tam kurun
8. kuyruk, cache ve session suruculerini uretim altyapisina gore ayarlayin
9. dosya ve log izinlerini dogrulayin
10. Filament admin erisimini sadece gerekli kisilere birakin

## 20. Projeyi Ayni Sekilde Yeniden Yapmak Isteyenler Icin Kisa Ozet

Bu sistemi sifirdan yeniden yapmak istiyorsaniz su kombinasyonu kurmaniz gerekir:

- Laravel 13
- PHP 8.3
- Blade tabanli arayuz
- Tailwind CSS 4 + Vite 8
- LDAP dogrulama icin LdapRecord-Laravel
- QR uretimi icin Endroid QR Code
- Filament 5 admin paneli
- `users`, `departments`, `qr_codes`, `scan_analytics` veri modeli
- departman bazli erisim kurali
- kisa link yonlendirme + analitik kaydi
- theme toggle, dashboard filtreleri ve QR modal onizleme

Yani proje ozunde bir "kurumsal dinamik QR yonetim sistemi"dir; LDAP dogrulama, departman yetkilendirmesi, kisa link yonlendirmesi ve yonetim paneli birlikte calisir.
