<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangLingkupBadanUsaha extends Model
{
    use HasFactory;

    protected $table = 'ruang_lingkup_badan_usaha';

    protected $fillable = [
        'ruang_lingkup_id',
        'badan_usaha_id',
        'benefit',
        'cakupan',
        'jenis_layanan_ids',
        'obat_ids',
        'deskripsi',
    ];

    protected $casts = [
        'benefit' => 'array',
        'cakupan' => 'array',
        'jenis_layanan_ids' => 'array',
        'obat_ids' => 'array',
    ];

    public function ruangLingkup()
    {
        return $this->belongsTo(RuangLingkup::class);
    }

    public function badanUsaha()
    {
        return $this->belongsTo(BadanUsaha::class, 'badan_usaha_id');
    }
}
