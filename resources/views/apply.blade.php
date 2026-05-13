<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIS Admission Form | Cambridge International School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[linear-gradient(180deg,#e0f2fe_0%,#f8fafc_30%,#fefce8_100%)] text-gray-900">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Back to welcome page</a>
            <a href="{{ route('login') }}" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg">Portal Login</a>
        </div>

        <div class="grid gap-8 lg:grid-cols-[0.95fr,1.45fr]">
            <section class="rounded-[2rem] bg-[linear-gradient(145deg,#0f172a_0%,#1d4ed8_52%,#0f766e_100%)] p-8 text-white shadow-2xl sm:p-10">
                <p class="inline-flex rounded-full bg-white/15 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em]">Cambridge International School Warri</p>
                <h1 class="mt-6 text-4xl font-black leading-tight sm:text-5xl">Application for Admission and Placement</h1>
                <p class="mt-5 text-sm leading-7 text-white/85 sm:text-base">
                    This online form follows the school’s full CIS admission structure more closely: applicant bio, family details, school history, health notes, and the parent undertaking.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-semibold">What happens after submission</p>
                        <p class="mt-2 text-sm text-white/80">The application is saved to the database, appears in the admin admissions inbox, and sends an email notification to admin immediately.</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-semibold">Admissions workflow</p>
                        <p class="mt-2 text-sm text-white/80">Admin can mark your application as new, under review, approved, rejected, or contacted.</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-semibold">Admissions email</p>
                        <a href="mailto:admissions@cambridgeinternationalschoolwarri.com" class="mt-2 block break-all text-sm font-semibold text-amber-200 hover:text-amber-100">admissions@cambridgeinternationalschoolwarri.com</a>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-semibold">Helpful note</p>
                        <p class="mt-2 text-sm text-white/80">If a section does not apply, you can leave the optional fields blank. Required fields are marked with an asterisk.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-white/80 bg-white/95 p-6 shadow-2xl sm:p-8">
                @if(session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                        Please review the highlighted fields and submit again.
                    </div>
                @endif

                <form method="POST" action="{{ route('apply.store') }}" class="space-y-8">
                    @csrf

                    <div class="rounded-3xl border border-blue-100 bg-blue-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">1. Application Details</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Academic Year</label>
                                <input name="academic_year" value="{{ old('academic_year') }}" placeholder="e.g. 2026/2027" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Applying for Class *</label>
                                <select name="class_level" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                    <option value="">Select class</option>
                                    @foreach(['Creche','Nursery 1','Nursery 2','Nursery 3','Primary 1','Primary 2','Primary 3','Primary 4','Primary 5','Primary 6','JSS 1','JSS 2','JSS 3','SS 1','SS 2','SS 3'] as $level)
                                        <option value="{{ $level }}" {{ old('class_level') === $level ? 'selected' : '' }}>{{ $level }}</option>
                                    @endforeach
                                </select>
                                @error('class_level')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-emerald-100 bg-emerald-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">2. Applicant Information</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Applicant Full Name *</label>
                                <input name="student_name" value="{{ old('student_name') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                                @error('student_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Preferred Name</label>
                                <input name="preferred_name" value="{{ old('preferred_name') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Date of Birth</label>
                                <input type="date" name="student_date_of_birth" value="{{ old('student_date_of_birth') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Sex</label>
                                <select name="student_gender" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Select sex</option>
                                    <option value="male" {{ old('student_gender') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('student_gender') === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Nationality</label>
                                <input name="nationality" value="{{ old('nationality') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">State of Origin</label>
                                <input name="state_of_origin" value="{{ old('state_of_origin') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Religious Affiliation</label>
                                <input name="religious_affiliation" value="{{ old('religious_affiliation') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Native Language</label>
                                <input name="native_language" value="{{ old('native_language') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Other Languages Spoken</label>
                                <input name="other_languages" value="{{ old('other_languages') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Passport Country / Number</label>
                                <input name="passport_country_number" value="{{ old('passport_country_number') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-amber-100 bg-amber-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">3. Parent and Contact Details</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Primary Parent / Guardian Name *</label>
                                <input name="parent_name" value="{{ old('parent_name') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100" />
                                @error('parent_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Primary Email *</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100" />
                                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Phone / WhatsApp *</label>
                                <input name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100" />
                                @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Alternate Phone</label>
                                <input name="alternate_phone" value="{{ old('alternate_phone') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Parent Occupation</label>
                                <input name="parent_occupation" value="{{ old('parent_occupation') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Applicant Lives With</label>
                                <select name="applicant_lives_with" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">
                                    <option value="">Select option</option>
                                    @foreach(['Father','Mother','Both Parents','Legal Guardian','Stepmother','Stepfather','Other'] as $option)
                                        <option value="{{ $option }}" {{ old('applicant_lives_with') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Home Address *</label>
                            <textarea name="home_address" rows="3" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">{{ old('home_address') }}</textarea>
                            @error('home_address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-3xl border border-violet-100 bg-violet-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">4. Father Information</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <input name="father_name" value="{{ old('father_name') }}" placeholder="Father's full name" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            <input name="father_phone" value="{{ old('father_phone') }}" placeholder="Father's phone" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            <input type="email" name="father_email" value="{{ old('father_email') }}" placeholder="Father's email" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            <input name="father_company_name" value="{{ old('father_company_name') }}" placeholder="Company name" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            <input name="father_position_title" value="{{ old('father_position_title') }}" placeholder="Position title" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            <input name="father_office_phone" value="{{ old('father_office_phone') }}" placeholder="Office telephone" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            <div class="md:col-span-2">
                                <input type="email" name="father_office_email" value="{{ old('father_office_email') }}" placeholder="Office email" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100" />
                            </div>
                            <div class="md:col-span-2">
                                <textarea name="father_home_address" rows="2" placeholder="Home address" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">{{ old('father_home_address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-pink-100 bg-pink-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">5. Mother Information</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <input name="mother_name" value="{{ old('mother_name') }}" placeholder="Mother's full name" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            <input name="mother_phone" value="{{ old('mother_phone') }}" placeholder="Mother's phone" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            <input type="email" name="mother_email" value="{{ old('mother_email') }}" placeholder="Mother's email" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            <input name="mother_company_name" value="{{ old('mother_company_name') }}" placeholder="Company name" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            <input name="mother_position_title" value="{{ old('mother_position_title') }}" placeholder="Position title" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            <input name="mother_office_phone" value="{{ old('mother_office_phone') }}" placeholder="Office telephone" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            <div class="md:col-span-2">
                                <input type="email" name="mother_office_email" value="{{ old('mother_office_email') }}" placeholder="Office email" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100" />
                            </div>
                            <div class="md:col-span-2">
                                <textarea name="mother_home_address" rows="2" placeholder="Home address" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-100">{{ old('mother_home_address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-cyan-100 bg-cyan-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">6. Family Information</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Legal Guardian Name</label>
                                <input name="legal_guardian_name" value="{{ old('legal_guardian_name') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Family Hospital or Clinic</label>
                                <input name="family_hospital_clinic" value="{{ old('family_hospital_clinic') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100" />
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Siblings Details</label>
                            <textarea name="siblings_details" rows="3" placeholder="List siblings, ages, and current schools" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100">{{ old('siblings_details') }}</textarea>
                        </div>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm">
                                <input type="checkbox" name="has_siblings_applying" value="1" {{ old('has_siblings_applying') ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                                <span>Sibling(s) also applying to CIS this year</span>
                            </label>
                            <div>
                                <input name="siblings_applying_details" value="{{ old('siblings_applying_details') }}" placeholder="Names and grades of siblings applying" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100" />
                            </div>
                            <div>
                                <input name="transfer_state_town" value="{{ old('transfer_state_town') }}" placeholder="If transferring, indicate state / town" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-cyan-500 focus:outline-none focus:ring-4 focus:ring-cyan-100" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-lime-100 bg-lime-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">7. School Information</h2>
                        <div class="mt-4 grid gap-5 md:grid-cols-2">
                            <input name="current_school_name" value="{{ old('current_school_name') }}" placeholder="Present school" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" />
                            <input name="current_school_class" value="{{ old('current_school_class') }}" placeholder="Current class" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" />
                            <div class="md:col-span-2">
                                <textarea name="current_school_address" rows="2" placeholder="Current school address" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100">{{ old('current_school_address') }}</textarea>
                            </div>
                            <input name="current_school_phone" value="{{ old('current_school_phone') }}" placeholder="Current school phone" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" />
                            <input name="previous_school" value="{{ old('previous_school') }}" placeholder="Most recent previous school" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100" />
                        </div>
                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Previous Two Schools Attended</label>
                            <textarea name="previous_schools" rows="3" placeholder="List school names, addresses, and dates attended" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100">{{ old('previous_schools') }}</textarea>
                        </div>
                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Extracurricular Activities</label>
                            <textarea name="extracurricular_activities" rows="3" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-lime-500 focus:outline-none focus:ring-4 focus:ring-lime-100">{{ old('extracurricular_activities') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-rose-100 bg-rose-50/70 p-5">
                        <h2 class="text-lg font-black text-gray-900">8. Health, Learning, and History</h2>
                        <div class="mt-4 space-y-5">
                            <textarea name="learning_physical_limitation" rows="3" placeholder="Learning / physical limitation, if any" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100">{{ old('learning_physical_limitation') }}</textarea>
                            <textarea name="peculiar_illness" rows="3" placeholder="Any peculiar illness?" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100">{{ old('peculiar_illness') }}</textarea>
                            <textarea name="diagnostic_information" rows="3" placeholder="Diagnostic / educational testing information" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100">{{ old('diagnostic_information') }}</textarea>
                        </div>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm">
                                <input type="checkbox" name="has_been_suspended_or_dismissed" value="1" {{ old('has_been_suspended_or_dismissed') ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                <span>Applicant has ever been suspended or dismissed from a school</span>
                            </label>
                            <input name="suspension_details" value="{{ old('suspension_details') }}" placeholder="If yes, explain" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100" />

                            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm">
                                <input type="checkbox" name="previously_applied_to_cis" value="1" {{ old('previously_applied_to_cis') ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                <span>Applicant previously applied to CIS Warri</span>
                            </label>
                            <input name="previously_applied_year" value="{{ old('previously_applied_year') }}" placeholder="Year applied" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100" />

                            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm">
                                <input type="checkbox" name="previously_attended_cis" value="1" {{ old('previously_attended_cis') ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                <span>Applicant has ever attended CIS Warri</span>
                            </label>
                            <input name="previously_attended_year" value="{{ old('previously_attended_year') }}" placeholder="Year attended" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100" />

                            <input name="heard_about_cis_through" value="{{ old('heard_about_cis_through', old('how_heard_about_us')) }}" placeholder="I first heard about CIS through" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100" />
                            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm">
                                <input type="checkbox" name="applying_to_other_schools" value="1" {{ old('applying_to_other_schools') ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                <span>Applicant is also applying to another school</span>
                            </label>
                            <div class="md:col-span-2">
                                <input name="other_school_name" value="{{ old('other_school_name') }}" placeholder="If yes, which school?" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5">
                        <h2 class="text-lg font-black text-gray-900">9. Tell Us About Your Child</h2>
                        <div class="mt-4 space-y-5">
                            <textarea name="child_personality_notes" rows="5" placeholder="Personality, attitude toward school, special gifts, talents, interests, or any other information helpful for admissions" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">{{ old('child_personality_notes') }}</textarea>
                            <textarea name="message" rows="3" placeholder="Any additional note or question for the admissions team" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-slate-500 focus:outline-none focus:ring-4 focus:ring-slate-100">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-orange-200 bg-orange-50/80 p-5">
                        <h2 class="text-lg font-black text-gray-900">10. Parent Undertaking</h2>
                        <p class="mt-3 text-sm leading-7 text-gray-700">
                            I confirm that the information supplied in this admission form is true to the best of my knowledge. I understand that admissions, fees, attendance expectations, and school rules remain subject to Cambridge International School policies.
                        </p>
                        <label class="mt-4 flex items-start gap-3 rounded-2xl border border-orange-200 bg-white px-4 py-4 text-sm font-medium text-gray-800">
                            <input type="checkbox" name="undertaking_accepted" value="1" {{ old('undertaking_accepted') ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span>I have read and accept the parent undertaking and consent to this application being reviewed by the school.</span>
                        </label>
                        @error('undertaking_accepted')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0f172a_0%,#1d4ed8_60%,#f97316_100%)] px-6 py-4 text-base font-bold text-white shadow-xl transition hover:-translate-y-0.5 hover:shadow-2xl">
                        Submit Admission Application
                    </button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
