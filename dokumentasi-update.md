# Dokumentasi Update — Kanban Project Management App

> File ini mencatat semua perubahan yang sudah dilakukan terhadap aplikasi, kenapa perubahan itu diperlukan, dan cara kerja bagian-bagian baru. Ditulis untuk programmer lain (atau kamu sendiri 3 bulan lagi) yang perlu lanjut mengembangkan project ini tanpa harus reverse-engineer semuanya dari nol.
>
> Stack: **CodeIgniter 3.1.13**, PHP 8.2, MySQL (XAMPP), vanilla JS (fetch API) di sisi frontend — tidak ada framework JS/build step.

---

## Daftar Isi

1. [Ringkasan Cepat](#1-ringkasan-cepat)
2. [Cara Setup dari Nol](#2-cara-setup-dari-nol)
3. [Perbaikan Infrastruktur & Bug Dasar](#3-perbaikan-infrastruktur--bug-dasar)
4. [Struktur Database](#4-struktur-database)
5. [Sistem Login & Role (Anggota vs Atasan)](#5-sistem-login--role-anggota-vs-atasan)
6. [Modul Project & Kanban Board](#6-modul-project--kanban-board)
7. [Daftar Lengkap API Endpoint](#7-daftar-lengkap-api-endpoint)
8. [Struktur File & Model](#8-struktur-file--model)
9. [Tutorial: Cara Menambah Fitur Baru](#9-tutorial-cara-menambah-fitur-baru)
10. [File yang Sudah Tidak Terpakai (Legacy)](#10-file-yang-sudah-tidak-terpakai-legacy)
11. [Keterbatasan & TODO Selanjutnya](#11-keterbatasan--todo-selanjutnya)

---

## 1. Ringkasan Cepat

Sebelumnya aplikasi ini adalah **prototype UI murni** — semua data hardcode di controller (`dummyProjects()`, dst), tidak ada koneksi database, tidak ada login. Sekarang aplikasi sudah:

- Tersambung penuh ke MySQL (database `kanban`), tidak ada lagi data dummy.
- Punya sistem login dengan 2 role: **anggota** dan **atasan**, masing-masing punya batasan akses berbeda.
- Kanban board dengan card modal ala Trello: label warna, checklist interaktif, attachment (link & upload file beneran), member per-task, due date.
- Tampilan sudah dipercantik (shadow, warna avatar per-user, hover effect, dll — bukan lagi tampilan flat/polos).

---

## 2. Cara Setup dari Nol

Kalau kamu clone project ini di komputer baru:

```bash
# 1. Import struktur tabel dasar
mysql -u root kanban < database/schema.sql

# 2. Import migration tambahan (role, tanggal rencana/aktual project, dst)
mysql -u root kanban < database/migration_auth_roles.sql

# 3. (opsional) isi data contoh biar ada yang bisa dilihat
mysql -u root kanban < database/seed_sample.sql
```

> **Catatan urutan:** `schema.sql` sekarang sudah final/canonical (sudah termasuk tabel `users` dan `task_members`). File `migration_users_members.sql` adalah riwayat migrasi lama yang sudah "menyatu" ke `schema.sql` — kalau setup database baru dari kosong, **tidak perlu** menjalankan `migration_users_members.sql` lagi. Tapi `migration_auth_roles.sql` **wajib** dijalankan setelah `schema.sql` karena berisi kolom role, kolom tanggal rencana/aktual, dan akun demo.

Setelah itu, buka:

```
http://localhost/kanban/index.php/auth
```

**Akun demo** (password sama semua: `password123`):

| Email | Role | Akses project |
|---|---|---|
| `siti@example.com` | atasan | Semua project |
| `budi@example.com` | anggota | Hanya "Test Project (Entite Racine)" |
| `andi@example.com` | anggota | "Test Project" + "Migrasi Server Internal" |

---

## 3. Perbaikan Infrastruktur & Bug Dasar

Sebelum fitur bisa dibangun, ada beberapa bug dasar yang harus dibenerin dulu. Ini penting diketahui supaya tidak bingung kalau nemu kode yang "aneh" di core CodeIgniter.

### 3.1 PHP 8.2: "Creation of dynamic property" deprecated

CodeIgniter 3 dibuat sebelum PHP 8.2, dan PHP 8.2 melarang assign property ke object tanpa deklarasi eksplisit (`Creation of dynamic property X::$y is deprecated`). Ini muncul di beberapa core class CI3 karena pola "super object"-nya CI3 (controller menyimpan semua library/model sebagai property dinamis, misal `$this->db`, `$this->M_user`, dst — nama-namanya baru diketahui saat runtime, tidak bisa dideklarasikan di awal).

**Yang diperbaiki:**

| File | Fix |
|---|---|
| `system/core/URI.php` | Deklarasi eksplisit `public $config;` |
| `system/core/Router.php` | Deklarasi eksplisit `public $uri;` |
| `system/core/Controller.php` | Tambah attribute `#[\AllowDynamicProperties]` di class `CI_Controller` |
| `system/core/Loader.php` | Tambah attribute `#[\AllowDynamicProperties]` di class `CI_Loader` |
| `system/database/DB_driver.php` | Tambah attribute `#[AllowDynamicProperties]` di class `CI_DB_driver` |

**Kenapa dua pendekatan berbeda?** Kalau nama property-nya **tetap/pasti** (misal `CI_Router` selalu punya `$this->uri`), lebih rapi dideklarasikan eksplisit. Kalau nama property-nya **berubah-ubah tergantung apa yang di-load user** (misal `CI_Controller` bisa punya `$this->M_user`, `$this->M_project`, dst tergantung project), solusi yang benar adalah `#[\AllowDynamicProperties]` di class-nya — mendeklarasikan satu-satu tidak mungkin karena namanya memang dinamis by design.

> **Kalau nanti nemu error "Creation of dynamic property" lagi** di file core lain: cek dulu apakah nama property-nya tetap atau dinamis, lalu pakai pendekatan yang sesuai di atas. Jangan pakai `error_reporting()` untuk menyembunyikan warning — itu cuma menutupi gejala, bukan solusi.

### 3.2 Konfigurasi salah (`base_url`, kredensial DB)

- `application/config/config.php` → `base_url` sebelumnya `http://localhost/project-management-module/` (folder project lama), sekarang `http://localhost/kanban/`. **Kalau kamu deploy ke folder/domain lain, ini yang pertama harus diubah.**
- `application/config/database.php` → `username`/`password` kosong, sekarang `root` / `` (default XAMPP). `database` diisi `kanban`.
- `application/config/config.php` → `encryption_key` sebelumnya kosong (bikin session library CI3 error), sekarang diisi random hex string. **Kalau deploy ke server lain, generate ulang** dengan `bin2hex(random_bytes(16))` — jangan reuse key yang sama antar environment.

### 3.3 Routing

`application/config/routes.php` — CI3 tidak otomatis memetakan URI ke controller kalau nama controller-nya beda dari URI (`C_project.php` tidak otomatis kena URI `project/...` tanpa remap eksplisit). Route yang sudah ada:

```php
$route['project'] = 'C_project';
$route['project/detail/(:num)'] = 'C_project/detail/$1';
$route['project/dashboard/(:num)'] = 'C_project/dashboard/$1';
$route['project/(:any)'] = 'C_project/$1';

$route['auth'] = 'C_auth';
$route['auth/(:any)'] = 'C_auth/$1';

$route['default_controller'] = 'C_project';
```

> **Kalau bikin controller baru dengan prefix `C_` atau nama lain yang beda dari URI yang diinginkan, wajib tambah route seperti di atas.** Ini bug yang sempat kejadian saat bikin `C_auth` — lupa nambah route, hasilnya 404 terus padahal controller-nya sudah benar.

---

## 4. Struktur Database

Semua tabel ada di database `kanban`. Diagram relasi (disederhanakan):

```
users ──┬──< project_members >──┬── projects ──┬──< tasks ──┬──< task_labels >── labels
        │                       │              │            ├──< task_members >── users
        └──< task_activities >──┘              │            ├──< checklists ──< checklist_items
                                                 │            └──< attachments
                                                 └── (tanggal_rencana/aktual_*)
```

| Tabel | Fungsi | Kolom penting |
|---|---|---|
| `users` | Akun login | `role` ENUM('anggota','atasan'), `password` (bcrypt hash) |
| `projects` | Data project | `tanggal_rencana_mulai/selesai`, `tanggal_aktual_mulai/selesai` (buat monitoring atasan) |
| `project_members` | Siapa saja anggota di project mana | Dipakai untuk filter "project yang di-assign" ke anggota |
| `tasks` | Card di kanban | `status` ENUM('no_status','processing','closed'), `progress` 0-100 |
| `task_members` | Multi-assignee per task (many-to-many) | Beda dari `project_members` — ini per **task**, bukan per project |
| `labels` | Label warna, scope per-project | `color` hex string |
| `task_labels` | Pivot task ↔ label | — |
| `checklists` / `checklist_items` | Checklist per task, bisa lebih dari 1 checklist per card | `is_done` |
| `attachments` | Link atau file upload | `type` ENUM('link','file'), `url` (buat file = path/URL hasil upload) |
| `task_activities` | Log tiap aksi penting (basis Scoreboard) | `action`, `points` |

### Kenapa ada `task_members` DAN `project_members`?

- `project_members` = siapa yang **boleh akses** project itu (dipakai untuk role-based access control anggota).
- `task_members` = siapa yang **ditugaskan** ke card tertentu (fitur "Members" di card modal, seperti Trello).

Keduanya independen — seseorang bisa jadi `project_members` tapi tidak di-assign ke task manapun, atau sebaliknya (walau harusnya kalau di-assign ke task, dia juga harus ada di `project_members` — **belum ada validasi ini di kode**, lihat bagian [Keterbatasan](#11-keterbatasan--todo-selanjutnya)).

### File migrasi, urutan historis

1. `schema.sql` — struktur dasar (projects, tasks, labels, checklist, attachments, task_activities) + `users` + `task_members` (sudah digabung, canonical).
2. `migration_users_members.sql` — riwayat migrasi saat nambah `users`/`task_members` ke database yang **sudah berjalan** (bukan fresh install). Sudah ter-apply, disimpan sebagai riwayat saja.
3. `migration_auth_roles.sql` — nambah `role` ke `users`, nambah 4 kolom tanggal ke `projects`, plus set role & password demo account, plus seed 2 project tambahan (buat demo perbedaan akses anggota vs atasan).
4. `seed_sample.sql` — data contoh (1 project, task, checklist, label) buat testing awal.

---

## 5. Sistem Login & Role (Anggota vs Atasan)

### Alur

```
Anggota login → hanya lihat project yang di-assign (project_members) → buka Kanban → kerjakan task (checklist, attachment, geser status)
Atasan login  → lihat SEMUA project → cek detail (rencana vs aktual) → pantau Kanban/GANTT/Scoreboard (read-heavy, monitoring)
```

### File yang terlibat

| File | Peran |
|---|---|
| `application/core/MY_Controller.php` | Base controller — cek session, sediakan helper `require_login()`, `require_login_api()`, `is_atasan()`, `can_access_project()` |
| `application/controllers/C_auth.php` | Login form, proses login (`password_verify`), logout (`session->sess_destroy()`) |
| `application/views/auth/login.php` | Halaman login |
| `application/config/autoload.php` | Library `session` di-autoload |

### Cara kerja `MY_Controller` (PENTING dipahami)

Ini **bukan** nama sembarangan — di CodeIgniter 3, kalau ada file `application/core/MY_Controller.php` berisi `class MY_Controller extends CI_Controller`, maka **semua controller yang extends `MY_Controller`** (bukan `CI_Controller` langsung) otomatis dapat semua method di dalamnya. Prefix `MY_` ini diatur oleh `$config['subclass_prefix']` di `config.php` (defaultnya memang `MY_`, jangan diubah kecuali tahu konsekuensinya).

```php
// Controller baru WAJIB extends MY_Controller (bukan CI_Controller) kalau butuh cek login
class C_apapun extends MY_Controller
{
    public function halaman_rahasia()
    {
        if (!$this->require_login()) return; // otomatis redirect ke /auth kalau belum login
        // ...
    }

    public function api_endpoint()
    {
        if (!$this->require_login_api()) return; // otomatis kirim JSON error kalau belum login
        // ...
    }
}
```

Method yang tersedia dari `MY_Controller`:

| Method | Kegunaan |
|---|---|
| `$this->current_user` | Array `['id', 'name', 'email', 'role']` dari session, atau `null` kalau belum login |
| `$this->is_logged_in()` | bool |
| `$this->is_atasan()` | bool — `true` kalau role user = atasan |
| `$this->require_login()` | Dipakai di method yang **render halaman (view)**. Redirect ke `/auth` kalau belum login. |
| `$this->require_login_api()` | Dipakai di method **API/JSON**. Kirim `give_response('03', 'Silakan login terlebih dahulu')` kalau belum login. |
| `$this->can_access_project($project_id)` | `true` kalau atasan, atau kalau anggota & terdaftar di `project_members` project tsb |

> **Kenapa ada 2 fungsi beda untuk "wajib login" (`require_login` vs `require_login_api`)?** Karena controller ini punya 2 jenis method: yang me-render HTML (perlu redirect) dan yang balas JSON (perlu tetap balas JSON, browser fetch tidak bisa "redirect" dengan baik untuk API call). Selalu pilih yang sesuai jenis method-nya.

### Kenapa akses ditolak sampai level task/checklist/attachment, bukan cuma project?

Supaya anggota tidak bisa "menebak" ID task dari project lain lalu memanipulasinya lewat API langsung (curl/Postman), walau di UI tidak kelihatan tombolnya. Makanya di `C_project.php`, hampir semua endpoint yang menerima `task_id`/`checklist_id`/`item_id`/`attachment_id` akan **resolve dulu ke project_id**-nya lalu cek `can_access_project()`. Kalau nambah endpoint baru yang berhubungan dengan task, **selalu ikuti pola ini**, jangan cuma cek `project_id` di endpoint level project.

Contoh pola yang dipakai (lihat `updateTaskStatus()` di `C_project.php`):

```php
public function updateTaskStatus()
{
    if (!$this->require_login_api()) return;
    $task_id = $this->input->post('task_id');
    if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
        give_response('03', 'Anda tidak punya akses ke task ini');
        return;
    }
    // ... lanjut proses
}
```

### Role restriction tambahan (bukan cuma "boleh akses project atau tidak")

Selain akses per-project, ada 1 aturan role spesifik: **hanya atasan yang boleh mengubah data project** (`updateProjectDetail`, termasuk tanggal rencana/aktual). Dicek dengan `$this->is_atasan()` terpisah dari `can_access_project()`. Kalau nanti ada aturan role lain ("hanya atasan boleh X"), ikuti pola yang sama: cek akses project dulu, baru cek role.

### Batasan penting: `CURRENT_USER_ID` sudah tidak dipakai lagi

Versi sebelumnya sempat pakai konstanta `CURRENT_USER_ID = 1` (hardcode) sebagai pengganti sesi login sungguhan. **Sekarang semua sudah pakai `$this->current_user['id']`** dari session asli. Kalau nemu kode lain yang masih pakai `CURRENT_USER_ID`, itu artinya belum ter-update — ganti ke `$this->current_user['id']`.

---

## 6. Modul Project & Kanban Board

### Halaman yang aktif dipakai

| URL | Controller method | View | Fungsi |
|---|---|---|---|
| `/project` | `C_project::index()` | `projects_list.php` | Daftar project (di-filter per role) |
| `/project/detail/{id}` | `C_project::detail()` | `project_detail.php` | Form info project + rencana vs aktual (atasan only edit) |
| `/project/dashboard/{id}` | `C_project::dashboard()` | `dashboard.php` | **Halaman utama** — Kanban board + tab GANTT + Scoreboard, termasuk modal card detail |
| `/auth` | `C_auth::index()` | `auth/login.php` | Login |
| `/auth/logout` | `C_auth::logout()` | — | Hapus session, redirect ke `/auth` |

> `dashboard.php` adalah satu-satunya halaman yang berisi Kanban board, tab GANTT, dan tab Scoreboard sekaligus (pakai sidebar navigasi, bukan halaman terpisah). Lihat [bagian 10](#10-file-yang-sudah-tidak-terpakai-legacy) soal file `kanban.php`/`gantt.php`/`scoreboard.php` yang **tidak** dipakai lagi.

### Fitur card modal (di `dashboard.php`)

Saat card di-klik, modal terbuka dan fetch detail lewat `getTaskDetail`. Semua interaksi di dalam modal langsung panggil API (auto-save, tidak ada tombol "Simpan" global kecuali untuk field Deskripsi):

| Fitur | Endpoint yang dipanggil | Catatan |
|---|---|---|
| Ubah status (dropdown) | `updateTaskStatus` | Sama seperti drag-drop antar kolom |
| Tandai selesai (toggle bulat) | `toggleTaskComplete` | Set status `closed` + progress 100%, atau sebaliknya |
| Ubah judul | `updateTaskDetail` (on blur) | |
| Label (pilih/buat baru) | `attachLabel` / `detachLabel` / `createLabel` | Label baru otomatis langsung ter-attach ke card yang lagi dibuka |
| Due date | `updateTaskDetail` (on change) | |
| Members | `addTaskMember` / `removeTaskMember` | Daftar user diambil dari **semua** user (`getUsers`), bukan cuma `project_members` — lihat [Keterbatasan](#11-keterbatasan--todo-selanjutnya) |
| Deskripsi | `updateTaskDetail` (tombol Simpan manual) | |
| Checklist (multi checklist per card) | `addChecklist` / `deleteChecklist` / `addChecklistItem` / `toggleChecklistItem` / `deleteChecklistItem` | |
| Attachment link | `addAttachmentLink` | |
| Attachment file upload | `uploadAttachmentFile` | Multipart form-data, file disimpan di `uploads/attachments/` dengan nama ter-enkripsi (`encrypt_name: true` di config upload) |
| Hapus attachment | `deleteAttachment` | Kalau type=file, file fisik ikut dihapus dari disk |

### Poin otomatis & Scoreboard

Setiap kali task di-set status `closed` (baik lewat drag-drop, dropdown, atau toggle complete), sistem otomatis:
1. Set `progress = 100`.
2. Insert row ke `task_activities` dengan `action = 'task_closed'` dan `points = 10`.

Scoreboard (`getScoreboard`) meng-agregasi `SUM(points)` per user dari `task_activities`, di-filter per `project_id`. Kalau mau ubah skema poin (misal beda poin per prioritas task), ubah di `M_task::log_activity()` pemanggilnya di `C_project.php` (cari `log_activity(`).

---

## 7. Daftar Lengkap API Endpoint

Semua endpoint di bawah ini ada di `C_project.php` kecuali disebutkan lain. Semua butuh login (`require_login_api()`), dan yang menyentuh project/task tertentu selalu dicek `can_access_project()`.

**Auth** (`C_auth.php`)
| Method | Endpoint | Body |
|---|---|---|
| POST | `auth/login` | `email`, `password` |
| GET | `auth/logout` | — |

**Project**
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `project/getProjects` | Filter otomatis sesuai role |
| GET | `project/getProjectDetail?id=` | |
| POST | `project/updateProjectDetail` | **Atasan only** |
| GET | `project/getUsers` | Daftar semua user (buat member picker) |

**Kanban / Task**
| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `project/getKanban?project_id=` | Return kolom + task + label + member + checklist summary + attachment count |
| POST | `project/createTask` | `project_id`, `title` |
| POST | `project/updateTaskStatus` | `task_id`, `new_status` |
| POST | `project/toggleTaskComplete` | `task_id` |
| GET | `project/getTaskDetail?id=` | Full detail (labels, members, checklists+items, attachments) |
| POST | `project/updateTaskDetail` | `task_id`, `title`/`description`/`due_date` (partial update) |

**Label**
| Method | Endpoint |
|---|---|
| GET | `project/getLabels?project_id=` |
| POST | `project/createLabel` |
| POST | `project/attachLabel` |
| POST | `project/detachLabel` |

**Member (per task)**
| Method | Endpoint |
|---|---|
| POST | `project/addTaskMember` |
| POST | `project/removeTaskMember` |

**Checklist**
| Method | Endpoint |
|---|---|
| POST | `project/addChecklist` |
| POST | `project/deleteChecklist` |
| POST | `project/addChecklistItem` |
| POST | `project/toggleChecklistItem` |
| POST | `project/deleteChecklistItem` |

**Attachment**
| Method | Endpoint |
|---|---|
| POST | `project/addAttachmentLink` |
| POST | `project/uploadAttachmentFile` (multipart) |
| POST | `project/deleteAttachment` |

**Monitoring**
| Method | Endpoint |
|---|---|
| GET | `project/getGantt?project_id=` |
| GET | `project/getScoreboard?project_id=` |

**Format response** (dipakai semua endpoint lewat helper `give_response()` di `application/helpers/response_helper.php`):

```json
{ "code": "00", "message": "PESAN SUKSES", "data": { ... } }
```

Kode yang dipakai: `00` = sukses, `01` = error umum/validasi, `03` = tidak ada akses (belum login / bukan role yang sesuai / bukan anggota project tsb).

---

## 8. Struktur File & Model

```
application/
├── core/
│   └── MY_Controller.php       # Base controller: session + role guard
├── controllers/
│   ├── C_auth.php              # Login/logout
│   └── C_project.php           # Semua logic project/kanban (~30 method)
├── models/
│   ├── M_user.php              # all(), find(), find_by_email()
│   ├── M_project.php           # get_all(), get_all_for_user(), get_by_id(), update()
│   ├── M_task.php              # get_by_project(), get_detail(), create(), update_status(), update_fields(), log_activity(), get_project_id()
│   ├── M_label.php             # get_by_project(), create(), attach(), detach()
│   ├── M_checklist.php         # create(), delete(), add_item(), toggle_item(), delete_item(), find(), find_item()
│   ├── M_attachment.php        # add(), find(), delete()
│   └── M_member.php            # add(), remove()  -- ini utk task_members
├── views/
│   ├── auth/login.php
│   └── project/
│       ├── projects_list.php   # daftar project
│       ├── project_detail.php  # form detail + rencana vs aktual
│       └── dashboard.php       # Kanban + GANTT + Scoreboard (halaman utama, 1 file besar)
└── helpers/
    └── response_helper.php     # give_response()
```

**Kenapa modelnya dipecah per-domain (bukan 1 model besar)?** Supaya gampang di-trace: butuh query checklist → buka `M_checklist.php`, tidak perlu scroll file 500 baris. Kalau nambah domain baru (misal notifikasi), ikuti pola yang sama: 1 model = 1 tabel/domain utama.

---

## 9. Tutorial: Cara Menambah Fitur Baru

### 9.1 Menambah API endpoint baru di `C_project.php`

```php
public function contohEndpointBaru()
{
    try {
        if (!$this->require_login_api()) return;

        $task_id = $this->input->post('task_id');
        if (!$this->can_access_project($this->M_task->get_project_id($task_id))) {
            give_response('03', 'Anda tidak punya akses ke task ini');
            return;
        }

        // ... logic, panggil model ...

        give_response('00', 'BERHASIL', $data);
    } catch (Exception $exception) {
        give_response('01', $exception->getMessage());
    }
}
```

Lalu di JS (`dashboard.php`), panggil lewat helper `api()` yang sudah ada:

```js
const res = await api('contohEndpointBaru', { task_id: activeTaskId });
```

**Tidak perlu tambah route baru** — `routes.php` sudah punya `$route['project/(:any)'] = 'C_project/$1';` yang otomatis menangkap semua method baru di `C_project`.

### 9.2 Menambah tabel/kolom baru

1. Tambahkan `ALTER TABLE` atau `CREATE TABLE` di file migration baru, misal `database/migration_nama_fitur.sql`. **Jangan edit `schema.sql` langsung** kecuali kamu juga update file itu jadi cerminan struktur final (schema.sql idealnya selalu mencerminkan struktur "kalau setup dari nol").
2. Jalankan manual: `mysql -u root kanban < database/migration_nama_fitur.sql`
3. Update `schema.sql` supaya tetap jadi single source of truth untuk fresh install.
4. Tambah method di model terkait (atau bikin model baru kalau tabelnya benar-benar domain baru).

### 9.3 Menambah controller baru yang butuh login

```php
class C_nama_baru extends MY_Controller   // <- WAJIB extends MY_Controller, BUKAN CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('response');
    }

    public function halaman()
    {
        if (!$this->require_login()) return;
        $this->load->view('nama_view', ['user_name' => $this->current_user['name']]);
    }
}
```

Lalu tambahkan route di `routes.php` **kalau** nama controller beda dari URI yang mau dipakai (lihat [3.3](#33-routing)):

```php
$route['nama-url'] = 'C_nama_baru';
$route['nama-url/(:any)'] = 'C_nama_baru/$1';
```

### 9.4 Menambah role baru (selain anggota/atasan)

1. Ubah kolom `role` di `users` dari `ENUM('anggota','atasan')` jadi termasuk role baru (`ALTER TABLE users MODIFY role ENUM('anggota','atasan','role_baru') ...`).
2. Tambah helper di `MY_Controller.php`, misal `is_role_baru()` mengikuti pola `is_atasan()`.
3. Pakai helper itu di controller yang relevan.

---

## 10. File yang Sudah Tidak Terpakai (Legacy)

File-file berikut **ada di repo tapi tidak dipanggil dari controller manapun** (tidak ada route/method yang me-load-nya). Ini peninggalan versi prototype awal sebelum semuanya digabung jadi satu halaman `dashboard.php`:

- `application/views/project/kanban.php`
- `application/views/project/gantt.php`
- `application/views/project/scoreboard.php`

**Jangan edit file-file ini untuk nambah fitur** — perubahan tidak akan kelihatan di aplikasi karena tidak ada yang me-render-nya. Semua fitur Kanban/GANTT/Scoreboard sekarang ada di `dashboard.php` (dalam bentuk tab/panel). Kalau memang tidak diperlukan lagi, aman untuk dihapus — tapi belum dihapus di update ini supaya tidak menghilangkan kerjaan orang lain tanpa konfirmasi.

---

## 11. Keterbatasan & TODO Selanjutnya

Ini bagian paling penting buat programmer yang lanjut kerja di project ini — supaya tidak mengira sesuatu itu "bug" padahal memang belum dibangun:

1. **Tidak ada halaman "buat project baru"** — project baru & assign anggota masih manual lewat SQL (`INSERT INTO projects ...` + `INSERT INTO project_members ...`). Perlu dibuatkan UI-nya.
2. **Member picker di card modal menampilkan SEMUA user**, bukan cuma yang ada di `project_members` project tersebut. Idealnya di-filter dulu — sekarang siapapun bisa di-assign ke task walau bukan anggota project itu.
3. **Tidak ada validasi silang** antara `task_members` dan `project_members` (assign orang yang bukan anggota project ke task-nya tetap bisa lewat API, lihat poin 2).
4. **Tidak ada fitur lupa password / reset password.**
5. **Tidak ada halaman manajemen user** (tambah/edit/nonaktifkan akun) — semua masih manual lewat SQL.
6. **Password demo (`password123`) sama untuk semua akun** — ganti kalau mau dipakai lebih serius dari sekadar demo.
7. File-file legacy di [bagian 10](#10-file-yang-sudah-tidak-terpakai-legacy) sebaiknya dihapus atau digabung supaya tidak membingungkan.
8. `getGantt` dan `getScoreboard` tidak mem-validasi `project_id` kosong dengan pesan yang jelas (langsung return array kosong) — cukup untuk sekarang tapi bisa dirapikan.

---

*Dokumen ini dibuat mengikuti perubahan yang sudah diterapkan ke aplikasi per tanggal dokumentasi ini ditulis. Kalau ada perubahan besar berikutnya, update juga bagian yang relevan di file ini — jangan biarkan dokumentasi basi sementara kode terus berubah.*


---

## 12. Hak Akses Dinamis: User, Role, Permission, Audit Log

Menggantikan 2 role lama (`anggota`/`atasan`, enum di `users.role`) dengan role dan permission yang bisa diatur dari UI.

### Setup
Jalankan **sekali**, setelah `migration_auth_roles.sql`:

```bash
mysql -u root kanban < database/migration_rbac.sql
```

Efek: tabel baru `roles`, `permissions`, `role_permissions`, `audit_logs`; kolom `users.role` dihapus dan diganti `users.role_id` + `users.is_active`. Akun lama dipetakan: `atasan` -> **Atasan / Monitoring** (hanya lihat), `anggota` -> **R&D / MDS**. Akun **Super Admin** baru: `admin@example.com` / `password123` (**ganti segera**).

### Halaman
| URL | Controller | Permission | Fungsi |
|---|---|---|---|
| `/user` | `C_user` | `system.users` | Tambah user, edit nama/email/role, reset password, aktif/nonaktif |
| `/role` | `C_role` | `system.permissions` | Tambah/edit/hapus role (Super Admin terkunci) |
| `/permission` | `C_permission` | `system.permissions` | Centang hak akses per role |
| `/audit` | `C_audit` | `system.audit` | 200 aktivitas terakhir |
| `/akun` | `C_account` | login saja | Ubah profil & password sendiri |

### Daftar permission (10)
| Kode | Fungsi di aplikasi |
|---|---|
| `dashboard.view` | Dicadangkan (belum dipakai untuk membatasi halaman) |
| `project.view_all` | Melihat semua project (tanpa ini: hanya project yang user jadi member-nya) |
| `project.create` | `createProject` |
| `project.edit_own` | Edit project/task/label/checklist **hanya di project yang user jadi member-nya** |
| `doc.upload` | Tambah/hapus lampiran (link & file) |
| `doc.update_status` | Geser status task, tandai selesai, centang checklist |
| `doc.edit_others` | Memperluas `doc.upload` dan `doc.update_status` ke project yang user bukan member-nya |
| `system.users`, `system.permissions`, `system.audit` | Pengaturan sistem; **super_only**, tidak bisa diberikan ke role selain Super Admin |

### Cara kerja (`MY_Controller`)
- Role & permission dibaca dari DB **di setiap request** (bukan di session): perubahan hak akses dan penonaktifan akun langsung berlaku.
- Super Admin (`roles.is_system = 1`) selalu punya semua permission.
- Helper: `has_permission($code)`, `require_permission($code)` (halaman), `require_permission_api($code)` (JSON), `can_view_project()`, `can_edit_project()`, `can_update_status()`, `can_upload_doc()`. `can_access_project()` tetap ada sebagai alias `can_view_project()`.
- Endpoint **baca** memakai `can_view_project`; endpoint **ubah** dipisah ke `can_edit_project` / `can_update_status` / `can_upload_doc` (lihat pemetaan di `C_project.php`).
- View dashboard menyembunyikan tombol ubah sesuai izin (`no-edit`, `no-doc`, `no-status`), tapi itu hanya kosmetik; server tetap memeriksa.

### Menambah permission baru
1. `INSERT INTO permissions (...)` (kolom `group_key`: `main`/`doc`/`system`).
2. Cek di controller dengan `has_permission('kode.baru')`.
3. Muncul otomatis di halaman Hak Akses; tidak perlu ubah view.
