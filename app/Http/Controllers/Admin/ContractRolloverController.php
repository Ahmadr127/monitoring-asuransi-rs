<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuangLingkup;
use App\Services\ContractReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContractRolloverController extends Controller
{
    public function rollover(Request $request, RuangLingkup $contract, ContractReminderService $reminderService)
    {
        $validator = Validator::make($request->all(), [
            'new_end_date' => 'required|date|after:today',
            'new_start_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $oldEndDate = $contract->tanggal_berakhir;
            $oldStartDate = $contract->tanggal_mulai;
            $oldStatus = $contract->status;
            $newEndDate = $request->new_end_date;
            $newStartDate = $request->new_start_date ?? $contract->tanggal_mulai->format('Y-m-d');

            // Update kontrak
            $contract->update([
                'tanggal_mulai' => $newStartDate,
                'tanggal_berakhir' => $newEndDate,
                'status' => 'aktif',
            ]);

            // Simpan ke history
            $contract->contractHistory()->create([
                'action_type' => 'rollover',
                'old_end_date' => $oldEndDate,
                'new_end_date' => $newEndDate,
                'old_status' => $oldStatus,
                'new_status' => 'aktif',
                'notes' => $request->notes ?: 'Kerjasama diperpanjang',
                'metadata' => [
                    'old_start_date' => $oldStartDate?->format('Y-m-d'),
                    'new_start_date' => $newStartDate,
                ],
            ]);

            $contract->refresh();

            $reminderService->sendReminderForContract($contract);

            return response()->json([
                'success' => true,
                'message' => 'Kerjasama berhasil diperpanjang',
                'data' => [
                    'id' => $contract->id,
                    'nama_asuransi' => $contract->nama_asuransi,
                    'new_end_date' => $contract->tanggal_berakhir->format('Y-m-d'),
                    'new_start_date' => $contract->tanggal_mulai->format('Y-m-d'),
                    'days_remaining' => $contract->days_remaining,
                    'status' => $contract->status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Rollover failed: '.$e->getMessage(), [
                'contract_id' => $contract->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperpanjang kerjasama',
            ], 500);
        }
    }

    public function history(RuangLingkup $contract)
    {
        $history = $contract->contractHistory()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    public function updateExpiredContracts()
    {
        try {
            $expiredContracts = RuangLingkup::expiredButActive()->get();
            $updatedCount = 0;
            $expiredIds = [];

            foreach ($expiredContracts as $contract) {
                if ($contract->updateExpiredStatus()) {
                    $updatedCount++;
                    $expiredIds[] = $contract->id;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengupdate {$updatedCount} kerjasama yang expired",
                'updated_count' => $updatedCount,
                'expired_contracts' => $expiredIds,
            ]);
        } catch (\Exception $e) {
            Log::error('Update expired contracts failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate status',
            ], 500);
        }
    }

    public function statusCheck()
    {
        try {
            // Cache hasil selama 2 menit untuk mengurangi beban database
            $expiredContracts = cache()->remember('expired_contracts_check', 120, function () {
                return RuangLingkup::expiredButActive()->pluck('id')->toArray();
            });

            return response()->json([
                'success' => true,
                'expired_contracts' => $expiredContracts,
            ]);
        } catch (\Exception $e) {
            Log::error('Status check failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek status',
            ], 500);
        }
    }
}
