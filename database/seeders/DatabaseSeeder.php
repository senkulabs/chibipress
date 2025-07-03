<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@cms.test',
        ])->assignRole('admin');

        User::factory()->create([
            'name' => 'Editor',
            'email' => 'editor@cms.test',
        ])->assignRole('editor');

        User::factory()->create([
            'name' => 'Author',
            'email' => 'author@cms.test',
        ])->assignRole('author');

        $this->call([
            CMSSeeder::class
        ]);
    }
}
