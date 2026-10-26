# Project Management Module (Standalone) — CodeIgniter 3

Modul ini dibuat terpisah dari aplikasi internal kantor, mengikuti konvensi
yang sudah dipakai di `C_dashboard.php` (prefix `C_`, `give_response()`,
try-catch di tiap method). Tahap ini masih **dummy data**, belum konek DB.

## Isi folder

```
application/
  controllers/C_project.php      <- semua endpoint (Kanban, GANTT, Scoreboard)
  helpers/response_helper.php     <- give_response() (ASUMSI, cek ulang ke helper asli nanti)
  views/project/
    kanban.php                    <- board drag-drop
    gantt.php                     <- timeline task
    scoreboard.php                <- ranking anggota
database/schema.sql               <- draft tabel untuk tahap selanjutnya (setelah UI fix)
```

## Cara menjalankan (di local, terpisah dari app lama)

1. Download CodeIgniter 3 (framework core) dari https://codeigniter.com/ci3/ atau
   `composer create-project codeigniter/framework ci3-project "3.*"`
2. Copy 3 file/folder dari modul ini (`controllers/C_project.php`,
   `helpers/response_helper.php`, `views/project/`) ke dalam
   `application/` project CI3 yang baru di-download.
3. Buka `application/config/autoload.php`, pastikan `database` **tidak** perlu
   di-autoload dulu (karena masih dummy data, belum butuh koneksi DB).
4. Set `base_url` di `application/config/config.php` sesuai local server kamu.
5. Jalankan pakai PHP built-in server, contoh:
   ```
   php -S localhost:8000
   ```
6. Akses:
   - `http://localhost:8000/index.php/project` → Kanban
   - `http://localhost:8000/index.php/project/gantt_page` → GANTT
   - `http://localhost:8000/index.php/project/scoreboard_page` → Scoreboard

## Urutan pengembangan selanjutnya

1. ✅ UI Kanban + GANTT + Scoreboard (dummy data) — **tahap sekarang**
2. Validasi tampilan & flow ke user/tim
3. Jalankan `database/schema.sql` (sesuaikan dulu FK ke tabel `users` yang benar)
4. Ganti bagian dummy data di `C_project.php` jadi query asli
   (`$this->db->query(...)`, sama seperti pola di `C_dashboard.php`)
5. Tambah endpoint create/edit project & task (belum ada di versi ini)
6. Baru diintegrasikan ke aplikasi internal:
   - Pindahkan `C_project.php` → `application/controllers/` app lama
   - Pindahkan `response_helper.php` **hanya jika** helper `give_response()`
     belum ada di app lama (kalau sudah ada, pakai yang lama, hapus punya kamu)
   - Pindahkan `views/project/` → `application/views/` app lama
   - Sesuaikan routing di `application/config/routes.php`

## Catatan penting

- `give_response()` di sini masih **asumsi format**. Sebelum integrasi ke
  app lama, cek dulu isi helper aslinya (biasanya di
  `application/helpers/`) — kalau formatnya beda, cukup ubah satu file ini.
- Semua fetch di view masih pakai endpoint dummy (`getKanban`, `getGantt`,
  `getScoreboard`). Setelah DB siap, response JSON-nya harus tetap punya
  struktur field yang sama supaya JS di view tidak perlu diubah.
