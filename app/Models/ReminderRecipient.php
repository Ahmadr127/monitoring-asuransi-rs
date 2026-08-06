<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'category',
        'purpose',
        'insurance_name',
        'is_active',
        'last_manual_sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_manual_sent_at' => 'datetime',
    ];
}
