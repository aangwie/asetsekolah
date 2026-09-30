<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['superadmin', 'pengelola_aset', 'guru_staf'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@sekolah.test'],
            ['name' => 'Administrator', 'password' => 'password']
        );
        $admin->assignRole('superadmin');

        $pengelola = User::firstOrCreate(
            ['email' => 'pengelola@sekolah.test'],
            ['name' => 'Pengelola Aset', 'password' => 'password']
        );
        $pengelola->assignRole('pengelola_aset');
    }
}
