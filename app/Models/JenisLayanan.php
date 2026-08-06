<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisLayanan extends Model
{
    use HasFactory;

    protected $table = 'master_jenis_layanan';

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
    ];

    // Relasi Many-to-Many dengan RuangLingkup
    public function ruangLingkup()
    {
        return $this->belongsToMany(RuangLingkup::class, 'kerjasama_layanan', 'jenis_layanan_id', 'ruang_lingkup_id')
            ->withTimestamps();
    }
}
