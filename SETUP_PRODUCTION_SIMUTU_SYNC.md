# SETUP PRODUCTION — Sinkronisasi User dari Simutu

Dokumen langkah produksi untuk 3 aplikasi Laravel yang menarik data pegawai dari database **Simutu** (master data pegawai RS):

- `monitoring-asuransi-rs`    (DB: `monitor_asuransi`)
- `pengajuan-transportasi-rs` (DB: `laravel_transportasi_rsazra`)
- `suratpermintaan`           (DB: `suratpermintaan`)

Kode yang sudah terpasang di tiap aplikasi:

- Koneksi DB kedua `simutu` (read-only) di `config/database.php`
- Migrasi kolom `simutu_id`, `simutu_status`, `simutu_synced_at` di tabel `users`
- `config/simutu_sync.php` (pemetaan role per aplikasi)
- Service + command `php artisan simutu:sync-users [--dry-run]`
- Lazy-sync saat login + blokir login untuk user dengan status `non-aktif`
- Tombol "Sync dari Simutu" di halaman admin user

---

## 1. Arsitektur

```
+----------------+        (PostgreSQL, user read-only)
|    SIMUTU      | <--------------+
|  DB: simutu    |                | port 5432, schema public
| users, tbl_unit|                |
| tbl_role       |                |
+----------------+                |
                                  |
  +----------+  +-------------+  +---------------+
  | monitor_ |  |transportasi_|  |suratpermintaan|
  | asuransi |  | rsazra      |  |               |
  +----------+  +-------------+  +---------------+
```

Ketiga aplikasi hanya **membaca** `users`, `tbl_unit`, `tbl_role` dari simutu. Tidak pernah menulis ke DB simutu.

---

## 2. Siapkan role read-only di database Simutu (WAJIB di produksi)

Jangan pakai superuser `postgres`. Jalankan SQL berikut di server PostgreSQL simutu (sebagai admin):

```sql
CREATE ROLE simutu_ro LOGIN PASSWORD 'GANTI_PASSWORD_KUAT';

GRANT USAGE ON SCHEMA public TO simutu_ro;
GRANT SELECT ON public.users      TO simutu_ro;
GRANT SELECT ON public.tbl_unit   TO simutu_ro;
GRANT SELECT ON public.tbl_role   TO simutu_ro;
```

Verifikasi akses read-only:

```bash
PGPASSWORD='GANTI_PASSWORD_KUAT' psql -h <host-simutu> -U simutu_ro -d simutu -c "SELECT count(*) FROM users;"
# UPDATE public.users SET username = 'x' WHERE 1=0;
#   -> harus ERROR: permission denied   (bukti tidak bisa menulis)
```

> Catatan: jika nanti simutu menambah tabel yang dipakai sinkronisasi, GRANT `SELECT` tabel baru tersebut juga.

### Firewall / pg_hba.conf

- `postgresql.conf`: pastikan `listen_addresses` mengizinkan IP server aplikasi, misal `'localhost, <IP_APP>'`.
- `pg_hba.conf`: tambahkan baris untuk IP server aplikasi:

```
host    simutu    simutu_ro    <IP_APP>/32    md5
```

- Login ulang postgres setelah ubah file konfigurasi (`pg_ctl reload`) dan pastikan port `5432` terbuka dari server aplikasi.

---

## 3. Set variabel `.env` di MASING-MASING aplikasi

Buat `.env` dari `.env.example` lalu isi 6 variabel simutu di ketiga aplikasi:

```
SIMUTU_SYNC_ENABLED=true
SIMUTU_DB_CONNECTION=simutu
SIMUTU_DB_HOST=<IP_ATAU_HOST_SIMUTU>
SIMUTU_DB_PORT=5432
SIMUTU_DB_DATABASE=simutu
SIMUTU_DB_USERNAME=simutu_ro
SIMUTU_DB_PASSWORD=<PASSWORD_YANG_DIBUAT_LANGKAH_2>
```

> Jangan ubah `DB_DATABASE` (DB lokal) — hanya variabel `SIMUTU_*` yang menunjuk ke simutu.

---

## 4. Verifikasi koneksi ke simutu

Di tiap folder aplikasi, jalankan:

```bash
php artisan simutu:sync-users --dry-run
```

Output yang diharapkan (contoh):

```
Simutu sync selesai (dry-run, tanpa menulis): 331 dicek, 21 akun dibuat, 310 akun diperbarui, 331 employees disinkron.
```

Jika muncul error koneksi, periksa `LOG_CHANNEL`/`storage/logs` atau jalankan `php artisan tinker` dan panggil koneksi `simutu`.

---

## 5. Jalankan migrasi & sinkronisasi pertama

Di tiap aplikasi (urutan penting — migrasi dulu):

```bash
php artisan migrate --force
php artisan simutu:sync-users --dry-run   # review dulu
php artisan simutu:sync-users              # live
```

Jalankan di luar jam sibuk karena sync massal pertama menciptakan ratusan akun.

### Verifikasi singkat per aplikasi

- **Monitoring**: cek tidak ada user tanpa role (Middleware akan redirect user tanpa role ke login):
  ```bash
  php artisan tinker --execute="echo App\Models\User::whereNull('role_id')->count();"
  ```
  Harus `0`. (Jika tidak, tambahkan `default_role` di `config/simutu_sync.php` lalu sync ulang.)
- **Transportasi**: cek `unit_kerja`, `jabatan`, `simutu_status` terisi.
- **Suratpermintaan**: cek `users` dan `employees` bertambah; role lama tidak berubah.

---

## 6. Optimasi cache produksi

Setelah semua env benar, di tiap aplikasi:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> Penting: setiap kali mengubah `.env` atau `config/simutu_sync.php`, jalankan ulang `php artisan config:cache`.

---

## 7. Otomatisasi sync berkala (opsional, disarankan)

Lazy-sync saat login + tombol admin sudah cukup untuk operasional harian. Untuk data lebih sinkron tanpa menunggu login, tambahkan job berkala:

### Linux (crontab)

```bash
crontab -e
```

```
# tiap 5 menit
*/5 * * * * cd /var/www/monitoring && php artisan simutu:sync-users --quiet >> storage/logs/simutu-sync.log 2>&1
*/5 * * * * cd /var/www/transportasi && php artisan simutu:sync-users --quiet >> storage/logs/simutu-sync.log 2>&1
*/5 * * * * cd /var/www/suratpermintaan && php artisan simutu:sync-users --quiet >> storage/logs/simutu-sync.log 2>&1
```

(Atau via Laravel scheduler: daftarkan di `routes/console.php` dan panggil `php artisan schedule:run` dari cron tiap menit.)

### Windows (Task Scheduler)

1. Buat Task dengan trigger "Repeat every 5 minutes".
2. Action → Program: `C:\path\php.exe`, Arguments: `C:\path\to\artisan simutu:sync-users`.

> Mode aman: tetap gunakan `--dry-run` di minggu pertama, atau jadwalkan di luar jam kerja, sambil memantau log.

---

## 8. Keamanan & operasional

- **Akun dibuat/disuspend di Simutu** akan ikut ke 3 sistem. Non-aktifkan di simutu -> akun tidak bisa login di mana pun.
- **Role internal** (`driver`, `legal`, `unit_*`, dst.) tetap diatur per aplikasi dan tidak ditimpa sinkronisasi.
- **Jangan pernah hapus user lokal** secara langsung — ada relasi FK (`transport_requests.user_id`, `drivers.user_id`, `permintaans.user_id`). Sinkronisasi tidak pernah menghapus.
- **Password**: yang berlaku adalah password Simutu (hash bcrypt disalin). Reset password cukup dilakukan di Simutu.
- **Backup**: backup DB lokal + DB simutu (`pg_dump`) sesuai kebijakan RS.
- **Monitoring**: awasi `storage/logs/laravel.log` di tiap aplikasi untuk pesan `Simutu ... gagal` (misal koneksi putus). Lazy-sync yang gagal tidak menghalangi login.

---

## 9. Checklist go-live

- [ ] Role `simutu_ro` + GRANT SELECT dibuat di DB simutu
- [ ] Firewall/pg_hba mengizinkan server aplikasi ke port 5432
- [ ] `.env` berisi `SIMUTU_*` yang benar di 3 aplikasi
- [ ] `php artisan migrate --force` sukses di 3 aplikasi
- [ ] `simutu:sync-users --dry-run` tidak error
- [ ] Sync live pertama sukses; verifikasi user + role tiap aplikasi
- [ ] `config:cache` dijalankan ulang
- [ ] (Opsional) Cron / Task Scheduler untuk sync berkala
- [ ] Tes login: akun aktif masuk, akun `non-aktif` ditolak

## 10. Rollback (jika perlu)

Sinkronisasi bersifat **non-destruktif** (tidak menghapus), jadi rollback aman:

1. Set `SIMUTU_SYNC_ENABLED=false` di `.env` ketiga aplikasi.
2. `php artisan config:cache`.
3. Login tetap berjalan seperti sebelum integrasi (akun lokal + password lokal tetap dipakai).

Kolom `simutu_*` tidak mengganggu, boleh dibiarkan. Untuk membersihkan, jalankan `php artisan migrate:rollback --step=1` (hanya menghapus kolom tambahan, data user tetap ada).