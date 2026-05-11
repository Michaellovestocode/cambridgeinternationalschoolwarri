<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminParentController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();
        $students = User::where('role', 'student')
            ->with(['class', 'parents:id'])
            ->orderBy('name')
            ->get();

        $parentsQuery = User::where('role', 'parent')
            ->with('children.class')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('parent_phone_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('class_id'), function ($query) use ($request) {
                $query->whereHas('children', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            });

        $parents = $parentsQuery->latest()->get();

        $totalParents = $parents->count();
        $totalChildren = $parents->sum(fn ($parent) => $parent->children->count());
        $parentsWithChildren = $parents->filter(fn ($parent) => $parent->children->isNotEmpty())->count();
        $parentsWithoutChildren = $totalParents - $parentsWithChildren;

        return view('admin.parents.index', compact(
            'parents',
            'classes',
            'students',
            'totalParents',
            'totalChildren',
            'parentsWithChildren',
            'parentsWithoutChildren'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'parent_phone_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'parent',
            'parent_phone_number' => $validated['parent_phone_number'] ?? null,
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        ]);

        return back()->with('success', 'Parent account created. They can now login with the provided credentials.');
    }

    public function attachStudent(Request $request, User $parent)
    {
        abort_unless($parent->isParent(), 404);

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $student = User::where('id', $validated['student_id'])
            ->where('role', 'student')
            ->firstOrFail();

        $parent->children()->syncWithoutDetaching([$student->id]);

        return back()->with('success', "{$student->name} is now linked to {$parent->name}'s parent dashboard.");
    }

    public function detachStudent(User $parent, User $student)
    {
        abort_unless($parent->isParent(), 404);
        abort_unless($student->isStudent(), 404);

        $parent->children()->detach($student->id);

        return back()->with('success', "{$student->name} has been removed from {$parent->name}'s parent dashboard.");
    }
}
