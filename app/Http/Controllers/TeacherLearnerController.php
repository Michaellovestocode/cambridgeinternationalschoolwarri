<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherLearnerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isTeacher(), 403, 'Only teachers can view teaching class learners.');

        $search = trim((string) $request->input('search'));

        $classes = $request->user()
            ->teachingClasses()
            ->with(['students' => function ($query) use ($search) {
                $query
                    ->when($search !== '', function ($studentQuery) use ($search) {
                        $studentQuery->where(function ($subQuery) use ($search) {
                            $subQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%");
                        });
                    })
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('teacher.learners.index', compact('classes', 'search'));
    }
}
