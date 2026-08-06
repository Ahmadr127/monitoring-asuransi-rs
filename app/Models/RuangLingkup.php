<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangLingkup extends Model
{
    use HasFactory;

    const EXPIRING_SOON = 30;

    const EXPIRING_WARNING = 90;

    const CRITICAL_DAYS = 7;

    const SORT_INACTIVE = 999999;

    protected $table = 'ruang_lingkup';

    protected $fillable = [
        'nama_asuransi',
        'is_high_profile',
        'penjamin_category',
        'alias_versi_tera',
        'badan_usaha_id',
        'jenis_layanan',
        'benefit',
        'cakupan',
        'obat',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
        'jenis_layanan' => 'array',
        'benefit' => 'array',
        'cakupan' => 'array',
        'obat' => 'array',
        'is_high_profile' => 'boolean',
    ];

    // Relasi ke badan usaha (single, backward compatibility)
    public function badanUsaha()
    {
        return $this->belongsTo(BadanUsaha::class);
    }

    // Relasi ke pivot multi-BU (source of truth untuk benefit & cakupan per BU)
    public function badanUsahaPivot()
    {
        return $this->hasMany(RuangLingkupBadanUsaha::class);
    }

    public function diskon()
    {
        return $this->hasMany(Diskon::class);
    }

    public function penagihanPembayaran()
    {
        return $this->hasMany(PenagihanPembayaran::class);
    }

    public function contractHistory()
    {
        return $this->hasMany(ContractHistory::class);
    }

    // Relasi Many-to-Many dengan JenisLayanan
    public function jenisLayananRelasi()
    {
        return $this->belongsToMany(JenisLayanan::class, 'kerjasama_layanan', 'ruang_lingkup_id', 'jenis_layanan_id')
            ->withTimestamps();
    }

    // Relasi Many-to-Many dengan Obat
    public function obatRelasi()
    {
        return $this->belongsToMany(Obat::class, 'kerjasama_obat', 'ruang_lingkup_id', 'obat_id')
            ->withTimestamps();
    }

    // Date Calculations
    public function getDaysRemainingAttribute()
    {
        // Hitung dari awal hari ini sampai tanggal berakhir
        $daysRemaining = now()->startOfDay()->diffInDays($this->tanggal_berakhir->startOfDay(), false);

        // Jika hari ini adalah hari terakhir, return 0 (masih aktif)
        // Jika sudah lewat, return nilai negatif
        return $daysRemaining;
    }

    public function getIsExpiredAttribute()
    {
        return $this->tanggal_berakhir->startOfDay()->isPast();
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status === 'aktif' && !$this->is_expired) {
            return [
                'class' => 'active',
                'text' => 'Aktif',
                'color' => 'success',
            ];
        }

        return [
            'class' => 'expired',
            'text' => 'Expired',
            'color' => 'danger',
        ];
    }

    public function updateExpiredStatus()
    {
        if ($this->is_expired && $this->status === 'aktif') {
            $oldStatus = $this->status;
            $this->update(['status' => 'tidak_aktif']);

            // Simpan ke history
            $this->contractHistory()->create([
                'action_type' => 'status_change',
                'old_status' => $oldStatus,
                'new_status' => 'tidak_aktif',
                'notes' => 'Status otomatis diubah karena kontrak sudah expired',
            ]);

            return true;
        }

        return false;
    }

    // Scope untuk kerjasama yang akan expired (termasuk hari ini/0 hari)
    public function scopeExpiring($query, $days = self::EXPIRING_SOON)
    {
        return $query->where('status', 'aktif')
            ->whereDate('tanggal_berakhir', '>=', now()->startOfDay())
            ->whereDate('tanggal_berakhir', '<=', now()->addDays($days));
    }

    public function scopeExpiredButActive($query)
    {
        return $query->where('status', 'aktif')
            ->whereDate('tanggal_berakhir', '<', now()->startOfDay());
    }
}
