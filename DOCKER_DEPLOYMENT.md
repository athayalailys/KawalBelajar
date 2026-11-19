# Docker Deployment KawalBelajar

Panduan ini menjalankan Staging dan Production secara berdampingan dari satu source code, tetapi dengan container, jaringan, volume database, dan file environment yang terpisah.

## Arsitektur

```text
Staging:    browser :8081 -> Nginx -> PHP-FPM -> MySQL kawalbelajar_staging
Production: browser :8080 -> Nginx -> PHP-FPM -> MySQL kawalbelajar_production
```

Kedua stack memakai `Dockerfile` multi-stage. Tahap Node menjalankan `npm ci` dan `npm run build`; image PHP-FPM 8.4 membawa source Laravel, `vendor`, dan aset Vite hasil build. PHP 8.4 diperlukan oleh versi dependency yang terkunci di `composer.lock`. Nginx menggunakan salinan `public/` dari build yang sama. Database hanya tersedia di jaringan internal Compose dan tidak membuka port MySQL ke host. Port HTTP Nginx hanya terikat ke loopback host; untuk akses publik, teruskan dari reverse proxy yang menangani HTTPS/TLS.

Volume persisten Staging: `kawalbelajar_db_staging_data` dan `kawalbelajar_storage_staging`. Volume Production: `kawalbelajar_db_production_data` dan `kawalbelajar_storage_production`. Jangan menghapus volume Production saat memperbarui container.

## Persiapan

Gunakan Docker Engine dan Docker Compose v2. Dari root repositori, buat file lokal yang tidak dilacak Git:

```powershell
if (!(Test-Path .env.staging)) { Copy-Item .env.staging.example .env.staging }
if (!(Test-Path .env.production)) { Copy-Item .env.production.example .env.production }
```

Isi `DB_PASSWORD` dengan password database yang kuat dan unik. Buat `APP_KEY` untuk masing-masing environment menggunakan langkah pada bagian berikut, lalu isi secret Xendit, Sentry, GA4, SMTP, dan akun Administrator sesuai kebutuhan. File `.env.staging` dan `.env.production` diabaikan oleh Git; jangan masukkan secret ke file `.example`.

## Jalankan Staging (UAT)

Pada `.env.staging`, pertahankan `APP_ENV=staging`, `APP_DEBUG=false`, `APP_URL=http://localhost:8081` untuk penggunaan lokal (atau URL HTTPS staging di belakang reverse proxy), `DB_DATABASE=kawalbelajar_staging`, `SENTRY_ENVIRONMENT=staging`, dan `GA4_MEASUREMENT_ID=` kosong. Isi `XENDIT_SECRET_KEY` dengan secret **development/sandbox**. Jangan gunakan kredensial Xendit live di UAT.

```powershell
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging build
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging run --rm --no-deps app_staging php artisan key:generate --show
```

Salin key yang ditampilkan ke `APP_KEY` di `.env.staging`, lalu jalankan:

```powershell
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging up -d
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging exec app_staging php artisan migrate --force
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging exec app_staging php artisan db:seed --class=DatabaseSeeder --force
```

Buka [http://localhost:8081](http://localhost:8081). Seeder Staging menyiapkan akun dan data demo untuk UAT. Jangan menggunakan data tersebut sebagai data Production.

## Jalankan Production

Pada `.env.production`, gunakan `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-production-anda`, `DB_DATABASE=kawalbelajar_production`, `SENTRY_ENVIRONMENT=production`, serta Measurement ID GA4 Production yang sebenarnya. Isi hanya secret **live** Xendit. Ganti `ADMIN_INITIAL_EMAIL`, `ADMIN_INITIAL_NAME`, dan `ADMIN_INITIAL_PASSWORD`; password awal harus minimal 16 karakter. Jangan menyimpan kredensial aktual di repositori. Siapkan reverse proxy di host untuk meneruskan HTTPS ke `127.0.0.1:8080`; jangan publikasikan aplikasi langsung lewat HTTP.

```powershell
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production build
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production run --rm --no-deps app_production php artisan key:generate --show
```

Salin key yang ditampilkan ke `APP_KEY` di `.env.production`. Sebelum migrasi pertama pada database yang sudah berisi data, buat dan verifikasi backup database; salah satu migrasi memindahkan skor lama lalu menghapus tabel legacy `nilai_evaluasi`. Kemudian jalankan migrasi dan seeder Administrator saja:

```powershell
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production up -d
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production exec app_production php artisan migrate --force
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production exec app_production php artisan db:seed --class=ProductionAdministratorSeeder --force
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production exec app_production php artisan optimize
```

Buka [http://localhost:8080](http://localhost:8080). Untuk hosting publik, ubah `APP_URL` menjadi domain HTTPS dan tempatkan stack di belakang reverse proxy/TLS. `DatabaseSeeder` juga memeriksa `APP_ENV=production` dan hanya memanggil `ProductionAdministratorSeeder`; ia tidak memanggil seeder demo. Seeder Administrator membaca kredensial dari environment, idempoten, dan menolak password awal yang terlalu pendek.

## Operasional

Lihat status atau log per environment:

```powershell
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging ps
docker compose --env-file .env.staging -f docker-compose.staging.yml -p kawalbelajar-staging logs -f app_staging nginx_staging
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production ps
docker compose --env-file .env.production -f docker-compose.production.yml -p kawalbelajar-production logs -f app_production nginx_production
```

Untuk menghentikan stack tanpa menghapus database dan berkas unggahan, gunakan `docker compose ... down` tanpa opsi `-v`. Staging dan Production boleh aktif bersamaan; masing-masing memakai port dan volume bernama berbeda.

## Pengujian

Jalankan suite PHPUnit dari lingkungan pengembangan proyek:

```powershell
php artisan test
```

Seeder Production tidak dijalankan oleh suite test. Lingkungan test tetap memakai konfigurasi test Laravel dan tidak mengirim exception ke Sentry.
