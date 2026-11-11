<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedOwner();
    }

    /**
     * Create the owner's admin login from ADMIN_* values in .env.
     * Skipped when they are not set, so no default password ever ships.
     */
    private function seedOwner(): void
    {
        $email = config('bonomali.admin.email');
        $password = config('bonomali.admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set in .env: skipping owner user.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => config('bonomali.admin.name'), 'password' => $password],
        );
    }
}
