# Plan: Role & Permission, Manajemen Departemen, dan Workflow Persetujuan Dinamis

Project: monitoring-asuransi-rs
Referensi modul organisasi: starterpack-laravel (`organization_types`, `organization_units`, User `organization_unit_id`, `head_id`, `members()`)

---

## 1. Analisa Sidebar Saat Ini

**Masalah:** sidebar (`resources/views/partials/sidebar.blade.php`) **hardcoded** hanya membedakan 2 kondisi:
```blade
@if(auth()->user()->isAdmin())
    ... seluruh menu admin ...
@else
    ... menu user read-only ...
@endif
```
Akibatnya:
- Tidak ada granularity hak akses per role/permission. Semua non-admin mendapat menu identik.
- Tidak ada role yang bisa **input/edit kontrak** selain admin.
- Menambah/mengubah menu = edit hardcode di blade, rawan error dan tidak scalable.

**Solusi:** menu dikendalikan **capability-based** lewat helper `User::canAccess($cap)` (mapping role → capability di `config/access.php`), sidebar bertanya ke capability, bukan `isAdmin()`.

---

## 2. Manajemen Departemen (pola starterpack)

Struktur:
- `organization_types` — tipe unit (Direktorat, Divisi, Departemen, Sub-Departemen) dengan `name`, `display_name`, `level`.
- `organization_units` — unit/departemen: `name`, `code`, `type_id`, `parent_id`, `head_id` (kepala unit = user), `description`, `is_active`.
- `users.organization_unit_id` — relasi user → departemen; `Unit::head()`, `Unit::members()`.

Model:
- `OrganizationType`, `OrganizationUnit` (dengan `descendants()`, `full_path`, `scopeActive`, `scopeRoot`).
- `User::department()` + `organizationUnit()`; `RuangLingkup::creator()`.

UI (admin): CRUD tipe & unit, pilih kepala, tampilkan anggota, jalur hierarki.

---

## 3. Role Penginput Kontrak (bukan hanya admin)

Seeder role ditambah:
- `penginput` (key `inputor`) — bisa membuat & mengajukan kontrak (draft → submit approval), mengedit draft miliknya.
- Admin tetap punya seluruh akses; legal/direktur/manajer sebagai approver via workflow.

Capability mapping (config/access.php):
- `view_dashboard`
- `contract.manage` (full CRUD + rollover + master) → admin
- `contract.input` (buat/submit kontrak) → penginput, admin
- `contract.approve` (approve/reject antrean) → role approver (bisa dikonfigurasi; default admin + legal, plus pemegang step approval)
- `workflow.manage` → admin
- `department.manage` → admin

---

## 4. Workflow Persetujuan Dinamis

**Prinsip:** workflow dikonfigurasi di database (bukan hardcode), dijalankan oleh engine service.

### Tabel
- `workflow_definitions`: `name`, `code`, `description`, `is_active`.
- `workflow_steps`: `workflow_definition_id`, `step_order`, `name`,
  `approver_type` (enum: `role` | `user` | `department_head` | `department`),
  `approver_value` (role key / user_id / unit_id),
  `can_reject`, `skip_if` (JSON kondisi), `is_active`.
- `approval_requests`: `workflow_definition_id`, `subject_type`, `subject_id` (polimorfik → ruang_lingkup), `requested_by`, `current_step`, `status` (`in_review | approved | rejected | cancelled`), `submitted_at`, `decided_at`.
- `approval_steps`: `approval_request_id`, `workflow_step_id`, `step_order`, `title`, `approver_user_id` (hasil resolve dinamis), `status` (`pending | approved | rejected | skipped | cancelled`), `note`, `decided_by`, `decided_at`.

### Kolom pada ruang_lingkup
- `approval_status` (`draft | in_review | approved | rejected`, default `approved` agar data lama tetap tampil)
- `submitted_by`, `current_approval_request_id`.

### Engine (`App\Services\WorkflowService`)
- `start(Request, RuangLingkup)` → membuat approval_request + resolve approver tiap step (role / user langsung / kepala departemen terpilih / anggota departemen).
- `approve(req, step, user, note)` → lanjut step berikutnya; bila semua step approved → status `approved`, kontrak menjadi **aktif** (efektif).
- `reject(req, step, user, note)` → status `rejected`, kembali ke pengaju (bisa revisi & re-submit).
- `cancel(req, user)` → dibatalkan pengaju.
- `skip_if` diproses saat resolve (kondisi JSON mis. `is_high_profile=false` → step dilewati).
- Step approved di-"pending" sesuai urutan; pengguna yang dipilih **disimpan di approval_steps** untuk audit.

### Integrasi lifecycle
- Draft / in_review: tidak muncul sebagai kontrak aktif (exclude dari reminder, dashboard aktif, dsb).
- Approved: `status = aktif`, reminder & dashboard berjalan normal.
- Reject: status pengajuan `rejected`, data bisa diubah → re-submit (workflow baru).

### UI
- Pengaturan workflow (admin): tambah/hapus/urutkan **step secara dinamis**, tentukan approver per step (role / nama user / kepala departemen tertentu / departemen), kondisi skip.
- Antrean approval (approver): daftar pending milik saya, tombol Approve / Reject / Cancel + catatan, timeline.
- Form kontrak: tombol "Simpan Draft" & "Ajukan Persetujuan"; tampil status approval.

---

## 5. Arsitektur & Layer

```
Route (web.php)
  └─ Http/Controllers/Admin/*
       ├─ DepartmentController      (CRUD tipe+unit, pilih kepala)
       ├─ WorkflowController        (definisi & step dinamis)
       └─ ApprovalController        (antrean + aksi approve/reject/cancel)
  └─ Http/Controllers/Admin/RuangLingkupController (submit approval)
Services:
  └─ App\Services\WorkflowService   (state machine approval)
Enums:
  └─ App\Enums\ApprovalStatus, ApproverType
Models: RuangLingkup, OrganizationType, OrganizationUnit,
        WorkflowDefinition, WorkflowStep, ApprovalRequest, ApprovalStep
Config: config/access.php (role → capability)
```