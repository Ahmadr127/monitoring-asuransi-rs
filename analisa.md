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
6. Edge case & risiko
- Self-approval conflict → skip/block sebuah langkah bila approver = submitter.
- Approver kosong/deadlock (role tanpa user) → validasi resolusi approver saat submit + eskalasi setelah SLA terlewati.
- Data diedit saat sudah in_review → kunci edit / buat versi draft terpisah (cegah konflik).
- Audit lengkap (user, waktu, catatan) untuk traceability; tambah mekanisme revoke bila ada kesalahan.
7. Rekomendasi implementasi bertahap
1. MVP (1 tingkat): tambah approval_status, submitted_by, approved_by, approved_at; tombol Approve/Reject di admin; filter reminder & status-otomatis hanya untuk data approved.
2. Tahap 2 (dinamis): tabel workflow_*, konfigurasi langkah di DB, kondisi skip (high-profile & kategori penjamin), notifikasi approval.
3. Tahap 3 (lanjutan): approval paralel, SLA & eskalasi, approver dinamis via delegasi, dashboard approval terpisah, integrasi synk Simutu.