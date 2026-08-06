<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenagihanPembayaran extends Model
{
    use HasFactory;

    protected $table = 'penagihan_pembayaran';

    protected $fillable = [
        'ruang_lingkup_id',
        'jangka_waktu_hari',
        'tipe_hari',
        'keterangan',
    ];

    protected $appends = ['tanggal_reminder'];

    public function ruangLingkup()
    {
        return $this->belongsTo(RuangLingkup::class);
    }

    /**
     * Calculate reminder date based on contract end date minus payment period
     */
    protected function tanggalReminder(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->ruangLingkup || ! $this->ruangLingkup->tanggal_berakhir) {
                    return null;
                }

                if ($this->jangka_waktu_hari === null) {
                    return null;
                }

                $tanggalBerakhir = Carbon::parse($this->ruangLingkup->tanggal_berakhir);

                return $tanggalBerakhir->copy()->subDays((int) $this->jangka_waktu_hari);
            }
        );
    }
}
