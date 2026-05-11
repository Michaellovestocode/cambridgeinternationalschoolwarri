<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Exam;
use App\Models\Question;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed sessions and terms first
        $this->call(SessionSeeder::class);

        // Seed subjects
        $this->call(SubjectSeeder::class);

        // Create Classes
       // Create Classes (Year 7 to Year 12)
$year7 = SchoolClass::create([
    'name' => 'Year 7',
    'description' => 'Junior Secondary 1 / Grade 7'
]);

$year8 = SchoolClass::create([
    'name' => 'Year 8',
    'description' => 'Junior Secondary 2 / Grade 8'
]);

$year9 = SchoolClass::create([
    'name' => 'Year 9',
    'description' => 'Junior Secondary 3 / Grade 9'
]);

$year10 = SchoolClass::create([
    'name' => 'Year 10',
    'description' => 'Senior Secondary 1 / Grade 10'
]);

$year11 = SchoolClass::create([
    'name' => 'Year 11',
    'description' => 'Senior Secondary 2 / Grade 11'
]);

$year12 = SchoolClass::create([
    'name' => 'Year 12',
    'description' => 'Senior Secondary 3 / Grade 12'
]);

        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@school.com',
            'registration_number' => 'ADMIN001',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Teachers
        $teacher1 = User::create([
            'name' => 'Mr. Okafor Emmanuel',
            'email' => 'okafor@school.com',
            'registration_number' => 'TCH001',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $teacher2 = User::create([
            'name' => 'Mrs. Adeyemi Grace',
            'email' => 'adeyemi@school.com',
            'registration_number' => 'TCH002',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        // Create Students
        $students = [
              ['name' => 'Chioma Nwankwo', 'reg' => 'STD2024001', 'class' => $year12->id],
    ['name' => 'Emeka Obi', 'reg' => 'STD2024002', 'class' => $year12->id],
    ['name' => 'Fatima Ibrahim', 'reg' => 'STD2024003', 'class' => $year11->id],
    ['name' => 'Tunde Adebayo', 'reg' => 'STD2024004', 'class' => $year11->id],               
    ['name' => 'Blessing Okoro', 'reg' => 'STD2024005', 'class' => $year10->id],
    ['name' => 'Ahmed Yusuf', 'reg' => 'STD2024006', 'class' => $year9->id],
    ['name' => 'Grace Eze', 'reg' => 'STD2024007', 'class' => $year8->id],
    ['name' => 'David Okon', 'reg' => 'STD2024008', 'class' => $year7->id],
        ];
        

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'registration_number' => $student['reg'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'class_id' => $student['class'],
            ]);
        }

        // Create Sample Exam
        $exam = Exam::create([
            'title' => 'Computer Science Mid-Term Exam',
            'description' => 'First term mid-term examination',
            'subject' => 'Computer Science',
            'duration_minutes' => 60,
            'total_marks' => 50,
            'pass_mark' => 25,
            'instructions' => 'Answer all questions. Read carefully before answering.',
            'created_by' => $teacher1->id,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Assign exam to classes
        $exam->classes()->attach([$ss2->id, $ss3->id]);

        // Create Multiple Choice Questions
        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What does CPU stand for?',
            'question_type' => 'multiple_choice',
            'marks' => 2,
            'options' => [
                'A' => 'Central Processing Unit',
                'B' => 'Computer Personal Unit',
                'C' => 'Central Program Utility',
                'D' => 'Computer Processing Unit'
            ],
            'correct_answer' => 'A',
            'order' => 1,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Which of the following is an input device?',
            'question_type' => 'multiple_choice',
            'marks' => 2,
            'options' => [
                'A' => 'Monitor',
                'B' => 'Printer',
                'C' => 'Keyboard',
                'D' => 'Speaker'
            ],
            'correct_answer' => 'C',
            'order' => 2,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is the brain of the computer?',
            'question_type' => 'multiple_choice',
            'marks' => 2,
            'options' => [
                'A' => 'Hard Drive',
                'B' => 'RAM',
                'C' => 'CPU',
                'D' => 'ROM'
            ],
            'correct_answer' => 'C',
            'order' => 3,
        ]);

        // Create Fill in the Blank Questions
        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'The full meaning of RAM is ______.',
            'question_type' => 'fill_blank',
            'marks' => 3,
            'correct_answer' => 'Random Access Memory',
            'order' => 4,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'HTML stands for ______.',
            'question_type' => 'fill_blank',
            'marks' => 3,
            'correct_answer' => 'HyperText Markup Language',
            'order' => 5,
        ]);

        // Create Theory Question
        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Explain the difference between hardware and software with examples.',
            'question_type' => 'theory',
            'marks' => 10,
            'order' => 6,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What are the main components of a computer system? Describe each briefly.',
            'question_type' => 'theory',
            'marks' => 10,
            'order' => 7,
        ]);

        // Create Coding Question
        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Write a Python function that takes a number and returns whether it is even or odd.',
            'question_type' => 'coding',
            'marks' => 8,
            'order' => 8,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Write HTML code to create a simple form with name and email fields.',
            'question_type' => 'coding',
            'marks' => 10,
            'order' => 9,
        ]);

        echo "\n✓ Database seeded successfully!\n\n";
        echo "====================================\n";
        echo "LOGIN CREDENTIALS:\n";
        echo "====================================\n\n";
        echo "ADMIN:\n";
        echo "Email: admin@school.com\n";
        echo "Password: password\n\n";
        echo "TEACHERS:\n";
        echo "Email: okafor@school.com | Password: password\n";
        echo "Email: adeyemi@school.com | Password: password\n\n";
        echo "STUDENTS:\n";
        echo "Reg No: STD2024001 | Password: password\n";
        echo "Reg No: STD2024002 | Password: password\n";
        echo "Reg No: STD2024003 | Password: password\n";
        echo "Reg No: STD2024004 | Password: password\n";
        echo "Reg No: STD2024005 | Password: password\n";
        echo "====================================\n\n";
    }
}
