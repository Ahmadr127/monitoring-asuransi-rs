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

class RuangLingkupController extends Controller
{
    use PreparesDashboardData;

    public function index()
    {
        RuangLingkup::expiredButActive()->get()->each(fn ($c) => $c->updateExpiredStatus());

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

        return view('admin.master.ruang_lingkup', compact('ruangLingkup', 'badanUsaha', 'visibleColumns'));
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
        $endDate = \Carbon\Carbon::parse($ruangLingkup->tanggal_berakhir);
        $daysRemaining = $now->diffInDays($endDate, false);
        $isExpired = $daysRemaining < 0;

        $ruangLingkup->days_remaining = max(0, $daysRemaining);
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
                    'badan_usaha_id' => $group['badan_usaha_id'],
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
                    'badan_usaha_id' => $group['badan_usaha_id'],
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
            'bu_groups' => 'required|array|min:1',
            'bu_groups.*.badan_usaha_id' => 'required|exists:master_badan_usaha,id',
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
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,tidak_aktif',
        ];

        $messages = [
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai',
            'bu_groups.*.jenis_layanan_ids.required' => 'Pilih minimal 1 jenis layanan untuk setiap Badan Usaha',
            'bu_groups.*.jenis_layanan_ids.min' => 'Pilih minimal 1 jenis layanan untuk setiap Badan Usaha',
            'bu_groups.required' => 'Minimal 1 Badan Usaha harus dipilih',
            'bu_groups.min' => 'Minimal 1 Badan Usaha harus dipilih',
            'bu_groups.*.badan_usaha_id.required' => 'Pilih Badan Usaha',
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
