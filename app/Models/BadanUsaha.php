<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadanUsaha extends Model
{
    use HasFactory;

    protected $table = 'master_badan_usaha';

    protected $fillable = [
        'nama_bu',
    ];

    // Daftar badan usaha high profile
    public static function getHighProfileList()
    {
        return [
            'PT Nayaka Era Husada',
            'PT Mandiri Inhealth',
            'PT Bukit Asam Tbk',
            'PT PLN (Persero)',
            'BPJS Ketenagakerjaan',
            'BPJS Kesehatan',
            'PT Kereta Api Indonesia (Persero)',
            'MEDIKA Yakespen (ANTAM)',
            'PT Telekomunikasi Indonesia (Telkom)',
        ];
    }

    // Cek apakah badan usaha ini high profile
    public function getIsHighProfileAttribute()
    {
        return in_array($this->nama_bu, self::getHighProfileList());
    }

    public function ruangLingkup()
    {
        return $this->hasMany(RuangLingkup::class);
    }
}
