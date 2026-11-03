<?php

namespace App\Enums;

use App\Models\User;
use App\Enums\UserType;

class AdminUser
{
    public static function superAdminUserId()
    {
        return User::where('type', UserType::SUPERADMIN)->pluck('id')->first();
    }
}

