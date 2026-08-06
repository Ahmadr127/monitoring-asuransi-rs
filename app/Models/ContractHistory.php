<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractHistory extends Model
{
    use HasFactory;

    protected $table = 'contract_history';

    protected $fillable = [
        'ruang_lingkup_id',
        'action_type',
        'old_end_date',
        'new_end_date',
        'old_status',
        'new_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'old_end_date' => 'date',
        'new_end_date' => 'date',
        'metadata' => 'array',
    ];

    public function ruangLingkup()
    {
        return $this->belongsTo(RuangLingkup::class);
    }
}
