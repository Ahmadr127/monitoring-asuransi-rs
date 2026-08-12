Berikut hasil analisa saya berdasarkan eksplorasi codebase monitoring-asuransi-rs (tidak ada perubahan kode).
A. Alur Monitoring Asuransi Saat Ini
1. Struktur data utama
- ruang_lingkup = tabel kerjasama asuransi (nama asuransi, is_high_profile, penjamin_category TPA/PAYOR/TPA&PAYOR, periode kontrak, status enum aktif|tidak_aktif).
- Benefit/cakupan/layanan/obat per badan usaha disimpan di pivot ruang_lingkup_badan_usaha (multi-BU).
- contract_history = audit log (created, updated, rollover, status_change, reminder_sent).
- master_badan_usaha, master_jenis_layanan, master_obat, diskon, penagihan_pembayaran = master data.
2. Alur operasional
- Input: Hanya role admin yang membuat/edit/menghapus kerjasama (RuangLingkupController, route role:admin). Legal & staff lain (user.kerjasama.*) hanya baca + export CSV. Form multi-langkah (ruang_lingkup_form.blade.php) dengan autosave "draft" di localStorage (ini draft UI, bukan draft data).
- Lifecycle kontrak:
1. Data dibuat langsung dengan status=aktif (langsung efektif).
2. Mendekati H-90/30/7 → ContractReminderService kirim email digest ke ReminderRecipient.
3. Lewat tanggal berakhir → updateExpiredStatus() otomatis ubah status=tidak_aktif (log status_change).
4. Perpanjangan → ContractRolloverController::rollover (admin isi tanggal baru, revisi jadi aktif, catat history).
3. Proses approval saat ini = TIDAK ADA.
- Tidak ada konsep persetujuan. Entri data hanya 1 langkah langsung ditulis oleh admin, tanpa draft-menunggu-reviu.
- Belum ada kolom created_by/submitted_by/approved_by/approved_at, dan contract_history tidak mencatat user_id — audit hanya aksi + timestamp.
- status (aktif/tidak_aktif) adalah status validitas kontrak, bukan status persetujuan.
4. Pembagian role & hak akses
Role didefinisikan di RoleSeeder: admin, legal, marketing, farmasi, kasir, pendaftaran. Pembagian akses saat ini masih kasar (2 kelompok besar):
- Admin (isAdmin): akses penuh (baca & tulis) — dashboard admin, CRUD kerjasama/ruang lingkup, master data (jenis layanan, obat, badan usaha, diskon, jangka waktu bayar/penagihan), import/export, perpanjangan kontrak (rollover) & history, manajemen user/role, pengaturan kolom tabel, penerima reminder, dan notifikasi.
- Non-admin (role:not_admin) — grup user, hanya baca:
  - legal → diarahkan ke user.dashboard; akses user.kerjasama.* (list, detail, export CSV) + modul notifikasi (route dibatasi role:admin,legal).
  - marketing, farmasi, kasir, pendaftaran → diarahkan ke user.kerjasama.index; akses user module yang sama (dashboard, data kerjasama, export CSV); TANPA akses notifikasi.
- Catatan penting: kelima role non-admin saat ini mendapat halaman & data yang identik (read-only). Belum ada pembedaan menu/aksi per divisi (mis. farmasi hanya melihat bagian obat, marketing hanya mengusulkan data, dst). Role hanya menentukan: (1) redirect awal di '/', (2) menu sidebar admin vs user, (3) izin membuka modul notifikasi.
- Konsekuensi untuk rencana approval: basis role untuk approver sudah tersedia (legal, marketing, farmasi, kasir, pendaftaran), tetapi workflow berjenjang membutuhkan konsep pejabat/tingkat tambahan (supervisor, manajer, direktur/komite) agar penetapan approver bisa dinamis per langkah.
5. Lifecycle kerjasama berdasarkan role (kondisi saat ini)
Tahapan lifecycle kontrak dan siapa yang terlibat:
- Penginputan / pembuatan data → hanya admin (langsung aktif, tanpa approval).
- Kontrak aktif (status=aktif) → semua role hanya melihat (admin juga melihat, non-admin read-only).
- Mendekati berakhir (H-90/30/7) → otomatis: ContractReminderService kirim email ke ReminderRecipient; tampil di dashboard semua role sebagai "akan berakhir". Tidak ada aksi manual role tertentu.
- Expired → otomatis: status=tidak_aktif (updateExpiredStatus). Admin melihat status; user read-only melihat "Expired".
- Perpanjangan / rollover → hanya admin.
Ringkasan per role:
- Admin: satu-satunya yang berperan aktif (input, edit, hapus, rollover, kelola master/pengaturan).
- Legal: pasif — melihat data, export CSV, dapat notifikasi.
- Marketing/Farmasi/Kasir/Pendaftaran: pasif — melihat data (list/detail/export), tanpa notifikasi, tanpa aksi apa pun.
- Kesimpulan: saat ini lifecycle hampir sepenuhnya "admin-driven"; role lain tidak memiliki tahapan kerja sendiri.
B. Rencana: Approval Berjenjang + Workflow Dinamis
1. Prinsip desain
Pisahkan dua hal: status kontrak (aktif/tidak_aktif, tetap) dan status persetujuan (baru: draft → in_review → approved | rejected). Workflow (siapa yang aproval di level mana, boleh skip atau tidak) dikonfigurasi di database, bukan hard-code — sehingga "dinamis".
2. Model data baru
- workflow_definitions — definisi template workflow per jenis transaksi:
code (mis. kerjasama_baru, rollover, ubah_data), is_active, triggers (JSON kondisi — mis. berlaku untuk high-profile saja).
- workflow_steps — langkah berjenjang:
workflow_definition_id, step_order, step_name, approver_type (role / user / dinamis), approver_value (key role / user_id), can_reject, skip_if (JSON kondisi, mis. lewati level 2 jika bukan high-profile), deadline_hours, is_parallel.
- approval_requests — instance pengajuan:
workflow_definition_id, subject_type/subject_id (ruang_lingkup), requested_by, status (draft,in_review,approved,rejected,cancelled), current_step, due_at.
- approval_steps — instansiasi per langkah (berisi per-approver jika paralel):
approval_request_id, workflow_step_id, approver_user_id, status (pending,approved,rejected,skipped), note, decided_at.
- Tambah kolom pada ruang_lingkup: approval_status, submitted_by, approved_at; dan user_id pada contract_history untuk audit lengkap.
3. Alur konkret (contoh "Kerjasama Baru")
Staff (legal/marketing) simpan draft (data belum efektif)
   → Ajukan Approval
   → Level 1: Supervisor Legal  — Approve
   → Level 2: Manajer (opsional, otomatis = HIGH PROFILE) — Approve
   → Level 3: Direktur/Komite (jika nilai/ketentuan tertentu) — Approve
   → activated: is_approved=true → terlihat aktif + reminder mulai berjalan
   → Reject di level mana pun → kembali ke submitter utk revisi & re-submit.
- Branching dinamis: level ekstra aktif/lewat berdasar kondisi JSON, mis. is_high_profile=true menambah level Direktur, penjamin_category="TPA & PAYOR" menambah verifikasi bagian keuangan, nilai kontrak >= X menambah level Komite. Jika submitter adalah admin → auto-skip level adminnya (anti self-approval).
- Contoh pemetaan role ke approval: marketing → usul/draft kerjasama; farmasi → verifikasi cakupan layanan & obat; legal → review isi kontrak (level 1); manajer/direktur → persetujuan akhir (level 2, opsional utk high-profile); kasir/pendaftaran → viewer saja (tanpa step approval).
- Rollover/perpanjangan juga lewat workflow (mis. 1–2 level), tanggal baru hanya efektif setelah disetujui.
4. Integrasi dengan fitur existing
- ContractReminderService & updateExpiredStatus hanya berjalan untuk data approved (izin efek kontrak). Data pending di-filter dari reminder & dashboard jumlah.
- contract_history diperkaya: action submitted/approved/step_approved/rejected/skipped + user_id, sehingga detail & timeline approval jadi satu sumber audit.
- Notifikasi (user_notifications sudah ada) dipakai: approver dapat notif "Menunggu persetujuan Anda", submitter dapat notif hasil approve/reject.
5. UI
- Form: tombol "Simpan Draft" & "Ajukan Persetujuan".
- Dashboard approver: antrean "Menunggu Persetujuan Saya", dengan diff perubahan data + tombol Approve/Reject + catatan.
- Timeline approval di halaman detail (menggabungkan contract history + approval steps).
- Menu Pengaturan: kelola workflow (tambah/edit langkah, tentukan approver, kondisi skip).
6. Lifecycle approval berdasarkan role (rencana)
Dengan workflow berjenjang, setiap tahap dipegang role tertentu sehingga lifecycle tidak lagi admin-driven:
- Inisiasi (Draft) → marketing (pengusul) & legal (input kontrak) membuat draft; data belum efektif.
- Verifikasi / Review teknis → farmasi mengecek cakupan layanan & obat; untuk TPA/PAYOR ditambah verifikasi bagian keuangan.
- Persetujuan level 1 (Review) → supervisor/legal menelaah isi kontrak.
- Persetujuan level 2 (Final, opsional) → manajer/direktur utk high-profile atau nilai besar.
- Aktif → setelah seluruh step approved, kontrak terlihat aktif & reminder mulai jalan.
- Monitoring → semua role melihat status (dashboard + detail).
- Perpanjangan → rollover masuk workflow lagi (1–2 level sesuai skenario).
- Kasir/pendaftaran → viewer saja (tanpa step, hanya melihat data aktif).
- Admin → pengelola sistem (konfigurasi workflow, override/skip), bukan penginput utama lagi.
Catatan: skenario di atas bisa diubah/dikonfigurasi per workflow_definition (urutan step, kondisi skip, approver-per-step), sehingga "lifecycle per role" mengikuti definisi dinamis, bukan kaku di kode.
7. Edge case & risiko
- Self-approval conflict → skip/block sebuah langkah bila approver = submitter.
- Approver kosong/deadlock (role tanpa user) → validasi resolusi approver saat submit + eskalasi setelah SLA terlewati.
- Data diedit saat sudah in_review → kunci edit / buat versi draft terpisah (cegah konflik).
- Audit lengkap (user, waktu, catatan) untuk traceability; tambah mekanisme revoke bila ada kesalahan.
8. Rekomendasi implementasi bertahap
1. MVP (1 tingkat): tambah approval_status, submitted_by, approved_by, approved_at; tombol Approve/Reject di admin; filter reminder & status-otomatis hanya untuk data approved.
2. Tahap 2 (dinamis): tabel workflow_*, konfigurasi langkah di DB, kondisi skip (high-profile & kategori penjamin), notifikasi approval.
3. Tahap 3 (lanjutan): approval paralel, SLA & eskalasi, approver dinamis via delegasi, dashboard approval terpisah, integrasi synk Simutu.