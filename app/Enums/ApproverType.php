<?php

namespace App\Enums;

enum ApproverType: string
{
    case ROLE = 'role';                  // approver_value = role key
    case USER = 'user';                  // approver_value = user id
    case DEPARTMENT_HEAD = 'department_head'; // approver_value = organization_unit_id
    case DEPARTMENT = 'department';      // any member of department (approver_value = organization_unit_id)

    public function label(): string
    {
        return match ($this) {
            self::ROLE => 'Berdasarkan Role',
            self::USER => 'User Langsung',
            self::DEPARTMENT_HEAD => 'Kepala Departemen',
            self::DEPARTMENT => 'Anggota Departemen',
        };
    }
}