<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Board;
use App\Models\Committee;
use App\Models\Field;
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
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        
        // Create admin
        Admin::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@iadcsuez.org',
            'password' => Hash::make('password'),
            'phone' => '1234567890',
        ]);

        // Create field for testing
        $field = Field::firstOrCreate([
            'name' => 'Test Field',
            'is_active' => true,
        ]);

        // Create committee for testing
        $committee = Committee::firstOrCreate([
            'name' => 'Test Committee',
            'field_id' => $field->id,
            'is_active' => true,
        ]);

        // Create board member for testing
        Board::firstOrCreate(
            ['email' => 'board@example.com'],
            [
                'name' => 'Board Member',
                'password' => Hash::make('password'),
                'phone' => '1234567890',
                'field_id' => $field->id,
                'committee_id' => $committee->id,
                'is_active' => true,
            ]
        );
    }
}

