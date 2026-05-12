<?php

namespace Database\Seeders;

use App\Models\Settings;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //User::factory()->create([
            //'name' => 'Test User',
            //'email' => 'test@example.com',
        //]);

        Settings::create(['name' => 'Site Name', 'night' => 0, 'logo' => 'logos/logo.jpg']);
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'domain' => '127.0.0.1:8000',
            'type' => 'admin',
            'password' => Hash::make('12345678'),
            'app_id' => Str::random(60),
        ]);
    }
}
