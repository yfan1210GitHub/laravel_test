<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\AccountStatus;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use SpatieRoleModel;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = User::firstOrCreate(
            [
                'id' => 1,
                'email' => 'admin@admin.com',
                'name' => 'Super Admin',
                'status' => "active",
                'password' => Hash::make('password'),
            ]
        );

        $adminUser->assignRole("Super Admin");
    }
}
