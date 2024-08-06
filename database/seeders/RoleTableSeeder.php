<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $role = new Role();
        // $role->name = 'Super Admin';
        // $role->guard_name = 'web';
        // $role->save();
        
        $role = new Role();
        $role->name = 'Admin';
        $role->guard_name = 'web';
        $role->save();
        
        $role = new Role();
        $role->name = 'Super Admin';
        $role->guard_name = 'web';
        $role->save();
        
        $role = new Role();
        $role->name = 'Employee';
        $role->guard_name = 'web';
        $role->save();

    }
}
