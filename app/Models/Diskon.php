<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskon extends Model
{
    use HasFactory;

    protected $table = 'diskon';

    protected $fillable = [
        'ruang_lingkup_id',
        'persentase_diskon',
        'keterangan',
    ];

    public function ruangLingkup()
    {
        return $this->belongsTo(RuangLingkup::class);
    }
}
