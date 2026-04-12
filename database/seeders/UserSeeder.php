<?php

namespace Database\Seeders;

use App\Models\ProfilMbg;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $super = User::query()->firstOrCreate(
            ['email' => 'super_admin@mbg.id'],
            [
                'name' => 'Super Admin',
                'password' => $password,
                'profil_mbg_id' => null,
                'status' => 'aktif',
            ]
        );
        $super->syncRoles(['super_admin']);

        $pusat = User::query()->firstOrCreate(
            ['email' => 'admin_pusat@mbg.id'],
            [
                'name' => 'Admin Pusat',
                'password' => $password,
                'profil_mbg_id' => null,
                'status' => 'aktif',
            ]
        );
        $pusat->syncRoles(['admin_pusat']);

        $profil = ProfilMbg::query()->where('kode_dapur', 'DP-JKT-01')->first();

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@mbg.id'],
            [
                'name' => 'Admin Dapur',
                'password' => $password,
                'profil_mbg_id' => $profil?->id,
                'status' => 'aktif',
            ]
        );
        $admin->syncRoles(['admin']);
    }
}
