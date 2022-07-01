<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Sentinel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('users')->truncate(); // Using truncate function so all info will be cleared when re-seeding.
        DB::table('roles')->truncate();
        DB::table('role_users')->truncate();

        $admin = Sentinel::registerAndActivate(array(
            'email'       => 'admin@admin.com',
            'username'       => 'admin',
            'password'    => "cherub",
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'emp_id' => 'UBSADMIN'
        ));
        $admin2 = Sentinel::registerAndActivate(array(
            'email'       => 'admin2@admin.com',
            'username'       => 'admin2',
            'password'    => "cherub",
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'emp_id' => 'UBSROOT'
        ));

        $adminRole = Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Super Administrator',
            'slug' => 'admin',
            'permissions' => array('admin' => 1),
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Super Administrator',
            'slug' => 'sub.admin'
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name'  => 'Branch User',
            'slug'  => 'branch.user',
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name'  => 'RCO',
            'slug'  => 'rco',
        ]);
        Sentinel::getRoleRepository()->createModel()->create([
            'name'  => 'Head Office User',
            'slug'  => 'head_office.user',
        ]);
        Sentinel::getRoleRepository()->createModel()->create([
            'name'  => 'System Access Control',
            'slug'  => 'sac',
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name'  => 'User',
            'slug'  => 'user',
        ]);

        $admin->roles()->attach($adminRole);
        $admin2->roles()->attach($adminRole);
    }
}
