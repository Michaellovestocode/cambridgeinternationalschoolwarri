<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentPortalSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('role', 'student')->first();
        if (!$student) {
            $this->command->warn('No student found; skipping parent creation.');
            return;
        }

        $parent = User::firstOrCreate(
            ['email' => 'parent@example.com'],
            [
                'name' => 'Parent User',
                'password' => Hash::make('password'),
                'role' => 'parent',
            ]
        );

        $parent->children()->syncWithoutDetaching([$student->id]);
        $this->command->info("Parent portal user created: parent@example.com / password (linked to {$student->name}).");
    }
}
