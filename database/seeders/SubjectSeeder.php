<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Junior Secondary Subjects
            ['name' => 'Mathematics', 'code' => 'MATH_J', 'category' => 'core', 'class_level' => 'junior', 'order' => 1],
            ['name' => 'English Language', 'code' => 'ENG_J', 'category' => 'core', 'class_level' => 'junior', 'order' => 2],
            ['name' => 'Basic Science', 'code' => 'BSC', 'category' => 'core', 'class_level' => 'junior', 'order' => 3],
            ['name' => 'Basic Technology', 'code' => 'BTECH', 'category' => 'elective', 'class_level' => 'junior', 'order' => 4],
            ['name' => 'Social Studies', 'code' => 'SOS', 'category' => 'core', 'class_level' => 'junior', 'order' => 5],
            ['name' => 'Civic Education', 'code' => 'CIV', 'category' => 'core', 'class_level' => 'junior', 'order' => 6],
            ['name' => 'Computer Science', 'code' => 'COMP_J', 'category' => 'elective', 'class_level' => 'junior', 'order' => 7],
            ['name' => 'Physical Education', 'code' => 'PE', 'category' => 'elective', 'class_level' => 'junior', 'order' => 8],
            ['name' => 'Home Economics', 'code' => 'HE', 'category' => 'vocational', 'class_level' => 'junior', 'order' => 9],
            ['name' => 'Agricultural Science', 'code' => 'AGR_J', 'category' => 'elective', 'class_level' => 'junior', 'order' => 10],

            // Senior Secondary Subjects
            ['name' => 'Mathematics', 'code' => 'MATH_S', 'category' => 'core', 'class_level' => 'senior', 'order' => 11],
            ['name' => 'English Language', 'code' => 'ENG_S', 'category' => 'core', 'class_level' => 'senior', 'order' => 12],
            ['name' => 'Physics', 'code' => 'PHY', 'category' => 'elective', 'class_level' => 'senior', 'order' => 13],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'category' => 'elective', 'class_level' => 'senior', 'order' => 14],
            ['name' => 'Biology', 'code' => 'BIO', 'category' => 'elective', 'class_level' => 'senior', 'order' => 15],
            ['name' => 'Further Mathematics', 'code' => 'FMATH', 'category' => 'elective', 'class_level' => 'senior', 'order' => 16],
            ['name' => 'Geography', 'code' => 'GEO', 'category' => 'elective', 'class_level' => 'senior', 'order' => 17],
            ['name' => 'Government', 'code' => 'GOV', 'category' => 'elective', 'class_level' => 'senior', 'order' => 18],
            ['name' => 'Economics', 'code' => 'ECON', 'category' => 'elective', 'class_level' => 'senior', 'order' => 19],
            ['name' => 'Commerce', 'code' => 'COMM', 'category' => 'elective', 'class_level' => 'senior', 'order' => 20],
            ['name' => 'Accounting', 'code' => 'ACC', 'category' => 'elective', 'class_level' => 'senior', 'order' => 21],
            ['name' => 'Literature in English', 'code' => 'LIT', 'category' => 'elective', 'class_level' => 'senior', 'order' => 22],
            ['name' => 'Computer Science', 'code' => 'COMP_S', 'category' => 'elective', 'class_level' => 'senior', 'order' => 23],
            ['name' => 'Technical Drawing', 'code' => 'TD', 'category' => 'elective', 'class_level' => 'senior', 'order' => 24],
            ['name' => 'Food and Nutrition', 'code' => 'FN', 'category' => 'vocational', 'class_level' => 'senior', 'order' => 25],
            ['name' => 'Agricultural Science', 'code' => 'AGR_S', 'category' => 'elective', 'class_level' => 'senior', 'order' => 26],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                $subject + ['is_active' => true]
            );
        }
    }
}