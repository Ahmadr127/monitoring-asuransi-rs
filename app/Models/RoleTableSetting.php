<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleTableSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'table_name',
        'visible_columns',
    ];

    protected $casts = [
        'visible_columns' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
