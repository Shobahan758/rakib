<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FurniturePolishSiteSettingsSeeder::class);

        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'shobahan758@gmail.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('bismillah'),
                'role' => 'super_admin',
            ],
        );
    }
}
