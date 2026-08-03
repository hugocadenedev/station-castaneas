<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the default superadmin account.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => env('CASTANEAS_SUPERADMIN_EMAIL', 'admin@castaneas.local')],
            [
                'name' => env('CASTANEAS_SUPERADMIN_NAME', 'Superadmin Castaneas'),
                'password' => Hash::make(env('CASTANEAS_SUPERADMIN_PASSWORD', 'ChangeMe123!')),
                'is_active' => true,
            ],
        );

        $user->syncRoles(['superadmin']);
    }
}