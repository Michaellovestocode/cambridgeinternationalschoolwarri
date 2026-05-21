@extends('layouts.app')

@section('title', 'Manage Classes')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">Manage Classes</h2>
            <p class="text-gray-600 mt-1 text-sm">Classes are grouped by school section and arranged naturally from the lowest level to the highest.</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-3 shadow border border-gray-100">
            <p class="text-xs font-bold uppercase text-gray-400">Total Classes</p>
            <p class="text-2xl font-black text-gray-900">{{ $classes->count() }}</p>
        </div>
    </div>

    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Add New Class</h3>
        
        <form action="{{ route('admin.class.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Class Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Year 7">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Stars, Science, Gold">
                @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:shadow-lg text-white px-6 py-3 rounded-xl font-bold transform hover:scale-105 transition">
                    Add Class
                </button>
            </div>
        </form>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-pink-100 bg-pink-50 px-4 py-3 text-sm font-bold text-pink-700">Creche: Creche 1 - 3</div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">Primary: Year 1 - 6</div>
            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700">Junior Secondary: Year 7 - 9</div>
            <div class="rounded-xl border border-purple-100 bg-purple-50 px-4 py-3 text-sm font-bold text-purple-700">Senior Secondary: Year 10 - 12</div>
        </div>
    </div>

    <div class="grid gap-6">
        @foreach($sectionDefinitions as $sectionKey => $section)
            @php
                $sectionClasses = $groupedClasses->get($sectionKey, collect());
            @endphp

            @if($sectionClasses->isNotEmpty())
                <section class="overflow-hidden rounded-3xl bg-white shadow-lg border border-gray-100">
                    <div class="bg-gradient-to-r {{ $section['color'] }} px-6 sm:px-8 py-5 text-white">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-2xl font-black">{{ $section['label'] }}</h3>
                                <p class="text-sm text-white/85">{{ $section['description'] }}</p>
                            </div>
                            <span class="rounded-full bg-white/20 px-4 py-2 text-sm font-black">{{ $sectionClasses->count() }} class{{ $sectionClasses->count() === 1 ? '' : 'es' }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:p-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($sectionClasses as $class)
                            <article class="rounded-2xl border border-gray-100 bg-gray-50/80 p-5 hover:bg-white hover:shadow-md transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase text-gray-400">Class</p>
                                        <h4 class="mt-1 text-xl font-black text-gray-900">{{ $class->display_name }}</h4>
                                        <p class="mt-1 text-sm text-gray-500">{{ $class->description ?: 'No description' }}</p>
                                    </div>
                                    @if($class->level_number)
                                        <span class="shrink-0 rounded-full border px-3 py-1 text-xs font-black {{ $section['soft'] }}">
                                            Level {{ $class->level_number }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-white px-4 py-3 border border-gray-100">
                                        <p class="text-xs font-bold text-gray-400">Students</p>
                                        <p class="text-2xl font-black text-blue-700">{{ $class->students_count }}</p>
                                    </div>
                                    <div class="rounded-xl bg-white px-4 py-3 border border-gray-100">
                                        <p class="text-xs font-bold text-gray-400">Exams</p>
                                        <p class="text-2xl font-black text-green-700">{{ $class->exams_count }}</p>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.class.edit', $class) }}" class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">Edit</a>
                                    <form action="{{ route('admin.class.delete', $class->id) }}" method="POST" onsubmit="return confirm('Delete this class?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg font-semibold transition">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if($classes->isEmpty())
            <div class="bg-white rounded-3xl shadow p-12 text-center">
                <div class="text-gray-400 text-lg">No classes yet</div>
                <p class="text-gray-500 text-sm mt-1">Add one above to get started.</p>
            </div>
        @endif
    </div>
</div>
@endsection
