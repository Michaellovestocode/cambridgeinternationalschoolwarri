@extends('layouts.app')

@section('title', 'Clinic Visit')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-wide text-blue-700">Clinic Visit Record</p><h1 class="mt-1 text-2xl font-black text-gray-900">{{ $visit->student->name }}</h1><p class="text-sm text-gray-500">{{ $visit->visited_at->format('j M Y, g:i A') }} · {{ $visit->student->class?->display_name }}</p></div><a href="{{ route('clinic.students.show', $visit->student) }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Back to History</a></div>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach([['Reason', $visit->reason], ['Outcome', str_replace('_', ' ', ucfirst($visit->outcome))], ['Temperature', $visit->temperature ? $visit->temperature . ' °C' : 'Not recorded'], ['Parent contacted', $visit->parent_contacted ? 'Yes' : 'No'], ['Contact time', $visit->parent_contacted_at?->format('j M Y, g:i A') ?: 'Not recorded'], ['Recorded by', $visit->recorder->name]] as $item)
            <div class="rounded-2xl bg-white p-5 shadow"><p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $item[0] }}</p><p class="mt-2 whitespace-pre-line font-semibold text-gray-900">{{ $item[1] }}</p></div>
        @endforeach
    </div>
    @foreach([['Symptoms / complaint', $visit->symptoms], ['Observation', $visit->observation], ['Action taken', $visit->action_taken], ['Medication administered', $visit->medication_administered], ['Additional notes', $visit->notes]] as $item)
        @if($item[1])<div class="rounded-2xl bg-white p-5 shadow"><h2 class="font-black text-gray-900">{{ $item[0] }}</h2><p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $item[1] }}</p></div>@endif
    @endforeach
</div>
@endsection
