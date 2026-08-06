<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    use HasFactory;

    protected $table = 'master_obat';

    protected $fillable = [
        'nama',
        'kategori',
        'satuan',
        'harga',
        'stok',
        'deskripsi',
    ];

    // Relasi Many-to-Many dengan RuangLingkup
    public function ruangLingkup()
    {
        return $this->belongsToMany(RuangLingkup::class, 'kerjasama_obat', 'obat_id', 'ruang_lingkup_id')
            ->withTimestamps();
    }
}
