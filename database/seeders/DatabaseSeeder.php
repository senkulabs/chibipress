<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            CategorySeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@cms.test',
            'password' => Hash::make('password'),
        ])->assignRole('admin');

        User::create([
            'name' => 'Editor',
            'email' => 'editor@cms.test',
            'password' => Hash::make('password'),
        ])->assignRole('editor');

        User::create([
            'name' => 'Author',
            'email' => 'author@cms.test',
            'password' => Hash::make('password'),
        ])->assignRole('author');

        $this->call([
            CMSSeeder::class
        ]);
    }
}
