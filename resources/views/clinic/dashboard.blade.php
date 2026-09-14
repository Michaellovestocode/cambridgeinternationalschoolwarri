@extends('layouts.app')

@section('title', 'Clinic Dashboard')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-cyan-700 p-6 text-white shadow-xl sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-100">Cambridge International School Warri</p>
                <h1 class="mt-2 text-3xl font-black">School Clinic</h1>
                <p class="mt-2 text-blue-100">Secure student care records and daily clinic activity.</p>
            </div>
            <a href="{{ route('clinic.visits.create') }}" class="rounded-xl bg-amber-400 px-5 py-3 text-center font-black text-slate-900 shadow hover:bg-amber-300">Record New Visit</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => "Today's Visits", 'value' => $todayCount, 'class' => 'bg-blue-50 text-blue-800'],
            ['label' => 'Visits This Week', 'value' => $weekCount, 'class' => 'bg-cyan-50 text-cyan-800'],
            ['label' => 'Currently in Clinic', 'value' => $inClinicCount, 'class' => 'bg-amber-50 text-amber-800'],
            ['label' => 'Sent Home Today', 'value' => $sentHomeCount, 'class' => 'bg-rose-50 text-rose-800'],
        ] as $stat)
            <div class="rounded-2xl {{ $stat['class'] }} p-5 shadow-sm">
                <p class="text-sm font-bold">{{ $stat['label'] }}</p>
                <p class="mt-2 text-4xl font-black">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr,0.7fr]">
        <section class="overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 p-5">
                <div><h2 class="text-xl font-black text-gray-900">Today's Clinic Visits</h2><p class="text-sm text-gray-500">Recent student care activity.</p></div>
                <a href="{{ route('clinic.students.index') }}" class="text-sm font-bold text-blue-700 hover:underline">Find Student</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($todayVisits as $visit)
                    <a href="{{ route('clinic.visits.show', $visit) }}" class="block p-5 hover:bg-blue-50">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="font-bold text-gray-900">{{ $visit->display_name }}</p><p class="text-sm text-gray-500">{{ $visit->person?->class?->display_name ?: ucfirst($visit->patient_type) }} · {{ $visit->reason }}</p></div>
                            <div class="text-left sm:text-right"><p class="text-sm font-bold text-gray-700">{{ $visit->visited_at->format('g:i A') }}</p><p class="text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($visit->outcome)) }}</p></div>
                        </div>
                    </a>
                @empty
                    <p class="p-8 text-center text-sm text-gray-500">No clinic visits recorded today.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-xl">
            <h2 class="text-xl font-black text-gray-900">Quick Actions</h2>
            <div class="mt-4 grid gap-3">
                <a href="{{ route('clinic.visits.create') }}" class="rounded-xl bg-amber-100 px-4 py-4 font-bold text-amber-900">Record New Visit</a>
                <a href="{{ route('clinic.students.index') }}" class="rounded-xl bg-blue-100 px-4 py-4 font-bold text-blue-900">Search Students</a>
                <a href="{{ route('clinic.incidents.index') }}" class="rounded-xl bg-rose-100 px-4 py-4 font-bold text-rose-900">Incident Reports</a>
                <a href="{{ route('clinic.inventory.index') }}" class="rounded-xl bg-violet-100 px-4 py-4 font-bold text-violet-900">Clinic Inventory{{ $lowStockCount ? ' · ' . $lowStockCount . ' low' : '' }}</a>
                <a href="{{ route('clinic.inventory.index') }}#supply-request" class="rounded-xl bg-amber-100 px-4 py-4 font-bold text-amber-900">Request Clinic Supplies</a>
            </div>
            <p class="mt-6 rounded-xl bg-slate-50 p-4 text-xs leading-5 text-slate-600">Clinic records are restricted to authorized clinic and administrator users.</p>
        </section>
    </div>
</div>
@endsection
