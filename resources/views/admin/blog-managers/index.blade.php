@extends('layouts.app')

@section('title', 'Blog Managers')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Blog Managers</h1>
            <p class="text-gray-500 mt-1">Manage users who can access Blog Studio without admin dashboard access.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.blog.index') }}" class="border border-gray-200 bg-white text-gray-700 px-5 py-3 rounded-xl font-semibold">Blog Studio</a>
            <a href="{{ route('admin.blog-managers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-semibold">Add Blog Manager</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-bold uppercase text-gray-500">Staff ID</th>
                    <th class="px-6 py-3 text-right text-xs font-bold uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($managers as $manager)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $manager->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $manager->email }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $manager->isTeacher() ? 'Teacher + Blog Manager' : 'Blog Manager' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $manager->registration_number ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($manager->isBlogManager())
                                <form method="POST" action="{{ route('admin.blog-managers.destroy', $manager) }}" onsubmit="return confirm('Delete this blog manager?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-bold text-rose-700 hover:bg-rose-50">Delete</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.blog-managers.revoke', $manager) }}" onsubmit="return confirm('Remove Blog Studio access from this teacher?');">
                                    @csrf
                                    @method('PUT')
                                    <button class="rounded-xl border border-amber-200 px-4 py-2 text-sm font-bold text-amber-700 hover:bg-amber-50">Revoke</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No blog managers have been created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
