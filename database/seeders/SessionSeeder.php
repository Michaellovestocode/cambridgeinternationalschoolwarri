<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Session;
use App\Models\Term;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create current academic session
        $session2025 = Session::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-08-31',
            'is_active' => true,
        ]);

        // Create terms for the current session
        Term::create([
            'session_id' => $session2025->id,
            'name' => 'First Term',
            'term_number' => 1,
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-20',
            'next_term_begins' => '2026-01-06',
            'is_active' => false,
        ]);

        Term::create([
            'session_id' => $session2025->id,
            'name' => 'Second Term',
            'term_number' => 2,
            'start_date' => '2026-01-06',
            'end_date' => '2026-04-10',
            'next_term_begins' => '2026-04-27',
            'is_active' => true, // Current term
        ]);

        Term::create([
            'session_id' => $session2025->id,
            'name' => 'Third Term',
            'term_number' => 3,
            'start_date' => '2026-04-27',
            'end_date' => '2026-08-31',
            'next_term_begins' => null,
            'is_active' => false,
        ]);

        // Create next academic session
        $session2026 = Session::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_active' => false,
        ]);

        // Create terms for next session
        Term::create([
            'session_id' => $session2026->id,
            'name' => 'First Term',
            'term_number' => 1,
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'next_term_begins' => '2027-01-06',
            'is_active' => false,
        ]);

        Term::create([
            'session_id' => $session2026->id,
            'name' => 'Second Term',
            'term_number' => 2,
            'start_date' => '2027-01-06',
            'end_date' => '2027-04-10',
            'next_term_begins' => '2027-04-27',
            'is_active' => false,
        ]);

        Term::create([
            'session_id' => $session2026->id,
            'name' => 'Third Term',
            'term_number' => 3,
            'start_date' => '2027-04-27',
            'end_date' => '2027-08-31',
            'next_term_begins' => null,
            'is_active' => false,
        ]);
    }
}
