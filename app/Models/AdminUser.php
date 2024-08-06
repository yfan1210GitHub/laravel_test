<?php

namespace App\Models;

use App\Enums\AuthGuard;

class AdminUser extends Authenticatable
{
    protected $guard_name = AuthGuard::API_ADMIN;
}
