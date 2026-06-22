<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classes = DB::table('school_classes')->get();
        $subjects = DB::table('subjects')
            ->where('is_active', true)
            ->whereNotNull('class_level')
            ->get();

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                if (! $this->subjectMatchesClass($subject->class_level, $class)) {
                    continue;
                }

                DB::table('class_subject')->updateOrInsert(
                    [
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Intentionally left blank so existing live class-subject assignments are not removed.
    }

    private function subjectMatchesClass(?string $classLevel, object $class): bool
    {
        if (! filled($classLevel)) {
            return false;
        }

        return in_array(
            strtolower(trim($classLevel)),
            array_map('strtolower', $this->classLevelCandidates($class)),
            true
        );
    }

    private function classLevelCandidates(object $class): array
    {
        $displayName = trim((string) $class->name . ' ' . (string) $class->description);
        $name = strtolower($displayName);
        $level = null;

        if (preg_match('/\b(?:year|yr)\s*[-]?\s*(\d{1,2})\b/', $name, $matches)) {
            $level = (int) $matches[1];
        } elseif (preg_match('/\bcreche\s*[-]?\s*(\d{1,2})\b/', $name, $matches)) {
            $level = (int) $matches[1];
        }

        $section = 'other';

        if (
            str_contains($name, 'creche')
            || str_contains($name, 'nursery')
            || str_contains($name, 'pre kg')
            || str_contains($name, 'pre-kg')
            || preg_match('/\bkg\b/', $name)
            || str_contains($name, 'kindergarten')
            || str_contains($name, 'reception')
        ) {
            $section = 'creche';
        } elseif ($level >= 1 && $level <= 6) {
            $section = 'primary';
        } elseif ($level >= 7 && $level <= 9) {
            $section = 'junior_secondary';
        } elseif ($level >= 10 && $level <= 12) {
            $section = 'senior_secondary';
        }

        $candidates = match ($section) {
            'creche' => ['creche', 'early years', 'nursery', 'kg', 'pre kg', 'pre-kg', 'all'],
            'primary' => ['primary', 'all'],
            'junior_secondary' => ['junior', 'jss', 'all'],
            'senior_secondary' => ['senior', 'sss', 'all'],
            default => ['all'],
        };

        foreach ([$class->name, $displayName] as $className) {
            if (filled($className)) {
                $candidates[] = trim((string) $className);
            }
        }

        if ($level) {
            $candidates = array_merge($candidates, match ($section) {
                'primary' => ["Primary {$level}", "Year {$level}", "Basic {$level}", "Pry {$level}"],
                'junior_secondary' => ["JSS {$level}", "Year {$level}"],
                'senior_secondary' => ["SSS {$level}", "Year {$level}"],
                'creche' => ["Creche {$level}", "Nursery {$level}", "KG {$level}"],
                default => [],
            });
        }

        return array_values(array_unique($candidates));
    }
};
