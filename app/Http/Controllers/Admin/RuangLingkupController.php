<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exceptions\WorkflowException;
use App\Models\BadanUsaha;
use App\Models\JenisLayanan;
use App\Models\Obat;
use App\Models\RuangLingkup;
use App\Models\RuangLingkupBadanUsaha;
use App\Services\ContractReminderService;
use App\Services\WorkflowService;
use App\Traits\PreparesDashboardData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RuangLingkupController extends Controller
{
    use PreparesDashboardData;

    public function index()
    {
        $ruangLingkup = RuangLingkup::with([
            'badanUsahaPivot.badanUsaha',
            'penagihanPembayaran',
            'obatRelasi',
            'diskon',
            'jenisLayananRelasi',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->flatMap(fn ($rl) => $rl->badanUsahaPivot->map(fn ($p) => [
                'rl' => $rl,
                'pivot' => $p,
                'bu_nama' => $p->badanUsaha->nama_bu ?? '',
                'benefit' => $p->benefit ?? [],
                'cakupan' => $p->cakupan ?? [],
            ]));

        $badanUsaha = BadanUsaha::orderBy('nama_bu', 'asc')->get();
        $visibleColumns = $this->getVisibleColumns(false);
        $rows = $this->buildRows($ruangLingkup);

        return view('admin.master.ruang_lingkup', compact('badanUsaha', 'visibleColumns', 'rows'));
    }

    public function create()
    {
        return view('admin.master.ruang_lingkup_form', $this->getMasterData());
    }

    public function edit(RuangLingkup $ruangLingkup)
    {
        $ruangLingkup->load('badanUsahaPivot.badanUsaha');

        $from = request('from');

        return view('admin.master.ruang_lingkup_form', array_merge(
            compact('ruangLingkup', 'from'),
            $this->getMasterData(),
        ));
    }

    public function show(RuangLingkup $ruangLingkup)
    {
        $ruangLingkup->load([
            'badanUsahaPivot.badanUsaha',
            'diskon',
            'penagihanPembayaran',
            'jenisLayananRelasi',
            'obatRelasi',
            'contractHistory',
        ]);

        $now = now();
        $endDate = $ruangLingkup->tanggal_berakhir
            ? \Carbon\Carbon::parse($ruangLingkup->tanggal_berakhir)
            : null;
        $daysRemaining = $endDate ? $now->diffInDays($endDate, false) : null;
        $isExpired = $daysRemaining !== null && $daysRemaining < 0;

        $ruangLingkup->days_remaining = $daysRemaining === null ? null : max(0, $daysRemaining);
        $ruangLingkup->is_expired = $isExpired;

        $visibleColumns = $this->getVisibleColumns(true);

        return view('admin.master.ruang_lingkup_detail', array_merge(
            compact('ruangLingkup', 'visibleColumns'),
            $this->getMasterData(),
        ));
    }

    public function store(Request $request, ContractReminderService $reminderService, WorkflowService $workflowService)
    {
        try {
            $validated = $this->validateRuangLingkupRequest($request);

            $validated['is_high_profile'] = $request->has('is_high_profile');
            $validated['created_by'] = auth()->id();

            // Auto-fill PIC dari user penginput bila dikosongkan.
            if (empty($validated['pic_nama'])) {
                $validated['pic_nama'] = auth()->user()->name;
            }
            if (empty($validated['pic_kontak'])) {
                $validated['pic_kontak'] = auth()->user()->no_telpon;
            }
            if (empty($validated['pic_email'])) {
                $validated['pic_email'] = auth()->user()->email;
            }

            $buGroups = $validated['bu_groups'];
            unset($validated['bu_groups']);

            $validated['badan_usaha_id'] = null;
            $validated['benefit'] = [];
            $validated['cakupan'] = [];
            $validated['jenis_layanan'] = [];
            $validated['obat'] = [];
            $validated['deskripsi'] = null;
            $validated['nama_asuransi'] = strtoupper($validated['nama_asuransi']);

            $ruangLingkup = RuangLingkup::create($validated);

            foreach ($buGroups as $group) {
                $ruangLingkup->badanUsahaPivot()->create([
                    'badan_usaha_id' => $group['badan_usaha_id'] ?? null,
                    'benefit' => $this->filterNonEmptyArray($group['benefit'] ?? []),
                    'cakupan' => $this->filterNonEmptyArray($group['cakupan'] ?? []),
                    'jenis_layanan_ids' => $this->filterNonEmptyArray($group['jenis_layanan_ids'] ?? []),
                    'obat_ids' => $this->filterNonEmptyArray($group['obat_ids'] ?? []),
                    'deskripsi' => $group['deskripsi'] ?? null,
                ]);
            }

            $this->syncBuGroupRelations($ruangLingkup, $buGroups);

            $ruangLingkup->contractHistory()->create([
                'user_id' => auth()->id(),
                'action_type' => 'created',
                'old_end_date' => null,
                'new_end_date' => $validated['tanggal_berakhir'],
                'old_status' => null,
                'new_status' => $validated['status'],
                'notes' => 'Kontrak awal dibuat',
                'metadata' => [
                    'old_start_date' => null,
                    'new_start_date' => $validated['tanggal_mulai'],
                ],
            ]);

            if (! auth()->user()->isAdmin() || $request->boolean('submit')) {
                $ruangLingkup->update(['approval_status' => 'draft']);
                $workflowService->start($ruangLingkup, auth()->user());

                return redirect()->route('admin.approvals.index')
                    ->with('success', 'Data disimpan dan diajukan untuk persetujuan.');
            }

            return redirect()->route('admin.ruang-lingkup.index')
                ->with('success', 'Data ruang lingkup berhasil ditambahkan');
        } catch (WorkflowException $e) {
            return redirect()->route('admin.ruang-lingkup.index')
                ->with('error', 'Data tersimpan, tetapi gagal mengajukan: '.$e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.ruang-lingkup.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal menambahkan data: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->route('admin.ruang-lingkup.index')
                ->with('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    public function update(Request $request, RuangLingkup $ruangLingkup, ContractReminderService $reminderService, WorkflowService $workflowService)
    {
        try {
            $validated = $this->validateRuangLingkupRequest($request, true);

            $validated['is_high_profile'] = $request->has('is_high_profile');
            $validated['updated_by'] = auth()->id();

            if (empty($validated['pic_nama'])) {
                $validated['pic_nama'] = auth()->user()->name;
            }
            if (empty($validated['pic_kontak'])) {
                $validated['pic_kontak'] = auth()->user()->no_telpon;
            }
            if (empty($validated['pic_email'])) {
                $validated['pic_email'] = auth()->user()->email;
            }

            $buGroups = $validated['bu_groups'];
            unset($validated['bu_groups']);

            $validated['badan_usaha_id'] = null;
            $validated['benefit'] = [];
            $validated['cakupan'] = [];
            $validated['jenis_layanan'] = [];
            $validated['obat'] = [];
            $validated['deskripsi'] = null;
            $validated['nama_asuransi'] = strtoupper($validated['nama_asuransi']);

            $ruangLingkup->update($validated);

            if ($ruangLingkup->wasChanged('tanggal_mulai') || $ruangLingkup->wasChanged('tanggal_berakhir') || $ruangLingkup->wasChanged('status')) {
                $ruangLingkup->contractHistory()->create([
                    'action_type' => 'updated',
                    'old_end_date' => $ruangLingkup->getOriginal('tanggal_berakhir'),
                    'new_end_date' => $ruangLingkup->tanggal_berakhir,
                    'old_status' => $ruangLingkup->getOriginal('status'),
                    'new_status' => $ruangLingkup->status,
                    'notes' => 'Data kontrak diperbarui',
                    'metadata' => [
                        'old_start_date' => $ruangLingkup->getOriginal('tanggal_mulai')?->format('Y-m-d'),
                        'new_start_date' => $ruangLingkup->tanggal_mulai?->format('Y-m-d'),
                    ],
                ]);

                $reminderService->sendReminderForContract($ruangLingkup->fresh());
            }

            $submittedPivotIds = collect($buGroups)->pluck('pivot_id')->filter();
            $ruangLingkup->badanUsahaPivot()
                ->whereNotIn('id', $submittedPivotIds)
                ->delete();

            foreach ($buGroups as $group) {
                $data = [
                    'badan_usaha_id' => $group['badan_usaha_id'] ?? null,
                    'benefit' => $this->filterNonEmptyArray($group['benefit'] ?? []),
                    'cakupan' => $this->filterNonEmptyArray($group['cakupan'] ?? []),
                    'jenis_layanan_ids' => $this->filterNonEmptyArray($group['jenis_layanan_ids'] ?? []),
                    'obat_ids' => $this->filterNonEmptyArray($group['obat_ids'] ?? []),
                    'deskripsi' => $group['deskripsi'] ?? null,
                ];

                if (! empty($group['pivot_id'])) {
                    RuangLingkupBadanUsaha::where('id', $group['pivot_id'])
                        ->where('ruang_lingkup_id', $ruangLingkup->id)
                        ->update($data);
                } else {
                    $ruangLingkup->badanUsahaPivot()->create($data);
                }
            }

            $this->syncBuGroupRelations($ruangLingkup, $buGroups);

            if ($request->boolean('submit')) {
                $workflowService->start($ruangLingkup, auth()->user());

                return redirect()->route('admin.approvals.index')
                    ->with('success', 'Data diperbarui dan diajukan untuk persetujuan.');
            }

            $redirectRoute = $request->input('from') === 'detail'
                ? route('admin.ruang-lingkup.show', $ruangLingkup)
                : route('admin.ruang-lingkup.index');

            return redirect($redirectRoute)
                ->with('success', 'Data ruang lingkup berhasil diupdate');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $redirectRoute = $request->input('from') === 'detail'
                ? route('admin.ruang-lingkup.edit', $ruangLingkup)
                : route('admin.ruang-lingkup.index');

            return redirect($redirectRoute)
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal mengupdate data: '.implode(', ', $e->validator->errors()->all()));
        } catch (WorkflowException $e) {
            $redirectRoute = $request->input('from') === 'detail'
                ? route('admin.ruang-lingkup.edit', $ruangLingkup)
                : route('admin.ruang-lingkup.index');

            return redirect($redirectRoute)
                ->with('error', 'Data tersimpan, tetapi gagal mengajukan: '.$e->getMessage());
        } catch (\Exception $e) {
            $redirectRoute = $request->input('from') === 'detail'
                ? route('admin.ruang-lingkup.edit', $ruangLingkup)
                : route('admin.ruang-lingkup.index');

            return redirect($redirectRoute)
                ->with('error', 'Gagal mengupdate data: '.$e->getMessage());
        }
    }

    public function destroy(RuangLingkup $ruangLingkup)
    {
        try {
            $ruangLingkup->delete();

            return redirect()->route('admin.ruang-lingkup.index')
                ->with('success', 'Data ruang lingkup berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.ruang-lingkup.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    /**
     * Ajukan kontrak (draft / reject) untuk persetujuan workflow.
     */
    public function submit(RuangLingkup $ruangLingkup, WorkflowService $workflowService)
    {
        try {
            $workflowService->start($ruangLingkup, auth()->user());

            return redirect()->route('admin.approvals.index')
                ->with('success', 'Kontrak berhasil diajukan untuk persetujuan.');
        } catch (WorkflowException $e) {
            return redirect()->route('admin.ruang-lingkup.show', $ruangLingkup)
                ->with('error', $e->getMessage());
        }
    }

    public function masterData()
    {
        return response()->json([
            'badan_usaha' => BadanUsaha::orderBy('nama_bu', 'asc')->get()->map(fn ($bu) => [
                'id' => $bu->id,
                'nama' => $bu->nama_bu,
            ]),
            'jenis_layanan' => JenisLayanan::orderBy('nama', 'asc')->get()->map(fn ($jl) => [
                'id' => $jl->id,
                'nama' => $jl->nama,
            ]),
            'obat' => Obat::orderBy('nama', 'asc')->get()->map(fn ($o) => [
                'id' => $o->id,
                'nama' => $o->nama,
            ]),
        ]);
    }

    /**
     * Bangun array polos per baris tabel (jauh lebih kecil dari HTML baris:
     * data dikirim via @json dan dirender jadi <tr> oleh JavaScript).
     */
    private function buildRows(\Illuminate\Support\Collection $items): array
    {
        $grouped = $items->groupBy(fn ($item) => $item['rl']->id);

        return $items->values()->map(function ($item) use ($grouped) {
            $rl = $item['rl'];
            $pivot = $item['pivot'];
            $buName = $item['bu_nama'];
            $benefitArray = $item['benefit'];
            $cakupanArray = $item['cakupan'];

            $pivotLayananIds = $pivot->jenis_layanan_ids ?? [];
            $jenisLayananArray = [];
            if (! empty($pivotLayananIds) && $rl->jenisLayananRelasi && $rl->jenisLayananRelasi->count() > 0) {
                $jenisLayananArray = $rl->jenisLayananRelasi->whereIn('id', $pivotLayananIds)->pluck('nama')->toArray();
            }

            $pivotObatIds = $pivot->obat_ids ?? [];
            $obats = ! empty($pivotObatIds) && $rl->obatRelasi
                ? $rl->obatRelasi->whereIn('id', $pivotObatIds)->values()
                : collect();
            $obatNames = $obats->pluck('nama')->map(fn ($n) => Str::limit($n, 25))->values()->all();

            $searchLayanan = strtolower(implode(' ', $jenisLayananArray));
            $searchBenefit = strtolower(implode(' ', $benefitArray));
            $searchCakupan = strtolower(implode(' ', $cakupanArray));
            $searchObat = strtolower($obats->pluck('nama')->implode(' '));

            $searchDiskon = '';
            if ($rl->diskon && $rl->diskon->count() > 0) {
                $searchDiskon = $rl->diskon->pluck('persentase_diskon')->implode(' ').' '.
                    strtolower($rl->diskon->pluck('keterangan')->implode(' '));
            }

            $allBuNames = $grouped->get($rl->id, collect())->pluck('bu_nama')->filter()->unique();

            $statusEffective = $rl->status_effective;
            $approvalBadge = ($rl->approval_status !== null && $rl->approval_status->value !== 'approved')
                ? $rl->approval_badge
                : null;

            $duration = ($rl->tanggal_mulai && $rl->tanggal_berakhir)
                ? $rl->tanggal_mulai->diffInDays($rl->tanggal_berakhir)
                : null;
            $remaining = $rl->tanggal_berakhir
                ? floor(now()->diffInDays($rl->tanggal_berakhir, false))
                : null;
            $remainingClass = $remaining !== null
                ? ($remaining <= 30 ? 'status-critical' : ($remaining <= 90 ? 'status-warning-badge' : 'status-active'))
                : '';

            return [
                'rl_id' => $rl->id,
                'nama' => Str::upper($rl->nama_asuransi),
                'alias' => $rl->alias_versi_tera,
                'nomor_spk' => $rl->nomor_spk,
                'penjamin' => $rl->penjamin_category,
                'high_profile' => $rl->is_high_profile ? '1' : '0',
                'bu_nama' => $buName !== '' ? Str::upper($buName) : '',
                'all_bu_names' => strtolower($allBuNames->implode(' ')),
                'tanggal_mulai' => $rl->tanggal_mulai?->format('Y-m-d'),
                'tanggal_berakhir' => $rl->tanggal_berakhir?->format('Y-m-d'),
                'mulai_fmt' => $rl->tanggal_mulai?->format('d M Y'),
                'berakhir_fmt' => $rl->tanggal_berakhir?->format('d M Y'),
                'expiration' => $rl->tanggal_berakhir ? (string) $rl->tanggal_berakhir : null,
                'duration_days' => $duration,
                'remaining_days' => $remaining !== null ? max(0, $remaining) : null,
                'remaining_class' => $remainingClass,
                'status_effective' => $statusEffective,
                'approval_label' => $approvalBadge['label'] ?? null,
                'approval_class' => $approvalBadge['class'] ?? null,
                'jenis_layanan' => $jenisLayananArray,
                'benefit' => $benefitArray,
                'cakupan' => $cakupanArray,
                'obat' => $obatNames,
                'diskons' => $rl->diskon ? $rl->diskon->pluck('persentase_diskon')->values()->all() : [],
                'penagihan' => $rl->penagihanPembayaran
                    ? $rl->penagihanPembayaran->map(fn ($p) => ['j' => $p->jangka_waktu_hari, 't' => $p->tipe_hari])->values()->all()
                    : [],
                'jangka_waktu' => $rl->penagihanPembayaran
                    ? implode(',', $rl->penagihanPembayaran->map(fn ($p) => $p->jangka_waktu_hari.'|'.$p->tipe_hari)->toArray())
                    : '',
                'search' => [
                    'nama' => strtolower($rl->nama_asuransi),
                    'alias' => strtolower($rl->alias_versi_tera ?? ''),
                    'nomor_spk' => strtolower($rl->nomor_spk ?? ''),
                    'layanan' => $searchLayanan,
                    'benefit' => $searchBenefit,
                    'cakupan' => $searchCakupan,
                    'obat' => $searchObat,
                    'deskripsi' => strtolower($rl->deskripsi ?? ''),
                    'diskon' => strtolower($searchDiskon),
                    'status' => $statusEffective,
                    'bu' => strtolower($buName),
                ],
                'show_url' => route('admin.ruang-lingkup.show', $rl),
                'edit_url' => route('admin.ruang-lingkup.edit', $rl),
                'destroy_url' => route('admin.ruang-lingkup.destroy', $rl),
                'penagihan_url' => route('admin.penagihan-pembayaran.index', ['ruang_lingkup_id' => $rl->id]),
                'diskon_url' => route('admin.diskon.index', ['ruang_lingkup_id' => $rl->id]),
            ];
        })->values()->all();
    }

    private function getMasterData(): array
    {
        return [
            'jenisLayanan' => JenisLayanan::orderBy('nama', 'asc')->get(),
            'masterObat' => Obat::orderBy('nama', 'asc')->get(),
            'badanUsaha' => BadanUsaha::orderBy('nama_bu', 'asc')->get(),
        ];
    }

    private function filterNonEmptyArray(?array $data): array
    {
        return array_values(array_filter($data ?? [], fn ($v) => ! empty($v)));
    }

    private function validateRuangLingkupRequest(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'nama_asuransi' => 'required|string|max:255',
            'is_high_profile' => 'nullable|boolean',
            'penjamin_category' => 'nullable|string|in:TPA,PAYOR,TPA & PAYOR',
            'alias_versi_tera' => 'nullable|string|max:255',
            'nomor_spk' => 'nullable|string|max:255',
            'pic_nama' => 'nullable|string|max:255',
            'pic_kontak' => 'nullable|string|max:30',
            'pic_email' => 'nullable|email|max:255',
            'pic_portal' => 'nullable|string|max:500',
            'alamat_penagihan' => 'nullable|string',
            'bu_groups' => 'required|array|min:1',
            'bu_groups.*.badan_usaha_id' => 'nullable|exists:master_badan_usaha,id',
            'bu_groups.*.benefit' => 'nullable|array',
            'bu_groups.*.benefit.*' => 'nullable|string',
            'bu_groups.*.cakupan' => 'nullable|array',
            'bu_groups.*.cakupan.*' => 'nullable|string',
            'bu_groups.*.jenis_layanan_ids' => 'required|array|min:1',
            'bu_groups.*.jenis_layanan_ids.*' => 'exists:master_jenis_layanan,id',
            'bu_groups.*.obat_ids' => 'nullable|array',
            'bu_groups.*.obat_ids.*' => 'exists:master_obat,id',
            'bu_groups.*.deskripsi' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,tidak_aktif',
        ];

        $messages = [
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai',
            'bu_groups.*.jenis_layanan_ids.required' => 'Pilih minimal 1 jenis layanan untuk setiap Badan Usaha',
            'bu_groups.*.jenis_layanan_ids.min' => 'Pilih minimal 1 jenis layanan untuk setiap Badan Usaha',
            'bu_groups.required' => 'Minimal 1 Badan Usaha harus dipilih',
            'bu_groups.min' => 'Minimal 1 Badan Usaha harus dipilih',
        ];

        if ($isUpdate) {
            $rules['bu_groups.*.pivot_id'] = 'nullable|exists:ruang_lingkup_badan_usaha,id';
        }

        return $request->validate($rules, $messages);
    }

    private function syncBuGroupRelations(RuangLingkup $ruangLingkup, array $buGroups): void
    {
        $allLayananIds = collect($buGroups)->flatMap(fn ($g) => $g['jenis_layanan_ids'] ?? [])->unique()->values()->toArray();
        $allObatIds = collect($buGroups)->flatMap(fn ($g) => $g['obat_ids'] ?? [])->unique()->values()->toArray();
        $ruangLingkup->jenisLayananRelasi()->sync($allLayananIds);
        $ruangLingkup->obatRelasi()->sync($allObatIds);
    }
}
