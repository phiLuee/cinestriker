<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Rollen erstellen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Berechtigungen erstellen
        Permission::firstOrCreate(['name' => 'manage movies']);
        Permission::firstOrCreate(['name' => 'register for ratings']);

        // Rechte zuweisen
        $adminRole->givePermissionTo(['manage movies', 'register for ratings']);
        $userRole->givePermissionTo('register for ratings');

        // Admin-User erstellen (Filament Zugang)
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('admin@example.com'),
            'email_verified_at' => null,
        ]);

        $admin->assignRole('user');
        $admin->assignRole('admin');

        // Optional: Test-Player-User erstellen
        $user = User::firstOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'User One',
            'password' => Hash::make('user@example.com'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('user');

        User::factory(100)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        $this->command->info('Admin- und User-Accounts wurden erfolgreich erstellt.');
    }
}
