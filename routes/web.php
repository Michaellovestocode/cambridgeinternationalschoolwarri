<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherScoreController;
use App\Http\Controllers\NigerianReportCardController;
use App\Http\Controllers\FormTeacherController;
use App\Http\Controllers\AdminAdmissionEnquiryController;
use App\Http\Controllers\AdmissionEnquiryController;
use App\Http\Controllers\AdminParentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementImageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\LearningSessionController;
use App\Http\Controllers\StudentLearningSessionController;
use App\Http\Controllers\AdminFeeClearanceController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BlogImageController;
use App\Models\Announcement;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    // Redirect authenticated users to their dashboard
    if (auth()->check()) {
        $user = auth()->user();
        
        if ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        } elseif ($user->isParent()) {
            return redirect()->route('parent.dashboard');
        } elseif ($user->isBlogManager()) {
            return redirect()->route('admin.blog.index');
        } elseif ($user->isTeacher() || $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
    }
    
    $announcements = Announcement::published()
        ->homepageOrder()
        ->take(3)
        ->get();

    $tickerAnnouncements = Announcement::published()
        ->where('show_in_ticker', true)
        ->homepageOrder()
        ->take(6)
        ->get();

    return view('welcome', compact('announcements', 'tickerAnnouncements'));
});

Route::get('/apply', [AdmissionEnquiryController::class, 'create'])->name('apply.create');
Route::post('/apply', [AdmissionEnquiryController::class, 'submitApplication'])->name('apply.store');
Route::post('/admission-enquiries', [AdmissionEnquiryController::class, 'store'])->name('admission-enquiries.store');
Route::get('/blog', [BlogPostController::class, 'publicIndex'])->name('blog.index');
Route::get('/blog/{post}', [BlogPostController::class, 'publicShow'])->name('blog.show');
Route::get('/blog-images/{path}', [BlogImageController::class, 'show'])
    ->where('path', '.*')
    ->name('blog-images.show');
Route::get('/announcement-images/{path}', [AnnouncementImageController::class, 'show'])
    ->where('path', '.*')
    ->name('announcement-images.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/report-cards/{reportCard}', [ParentPortalController::class, 'previewReportCard'])->name('report-cards.preview');
        Route::get('/messages', [MessageController::class, 'parentIndex'])->name('messages.index');
        Route::post('/messages', [MessageController::class, 'parentStore'])->name('messages.store');
    });

    // Student routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/learning-sessions', [StudentLearningSessionController::class, 'index'])->name('learning.index');
        Route::get('/learning-sessions/{learningSession}', [StudentLearningSessionController::class, 'show'])->name('learning.show');
        Route::post('/learning-sessions/{learningSession}/submit', [StudentLearningSessionController::class, 'submit'])->name('learning.submit');
        Route::get('/learning-attempts/{attempt}/result', [StudentLearningSessionController::class, 'result'])->name('learning.result');
        Route::get('/report-cards/{reportCard}', [StudentController::class, 'viewReportCard'])->name('report-cards.preview');
        Route::get('/exam/{exam}/start', [StudentController::class, 'startExam'])->name('start-exam');
        Route::get('/attempt/{attempt}', [StudentController::class, 'takeExam'])->name('take-exam');
        Route::post('/attempt/{attempt}/save', [StudentController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/attempt/{attempt}/submit', [StudentController::class, 'submitExam'])->name('submit-exam');
        Route::get('/attempt/{attempt}/result', [StudentController::class, 'viewResult'])->name('view-result');
        Route::get('/attempt/{attempt}/download-pdf', [StudentController::class, 'downloadResultPDF'])->name('download-result-pdf');
        Route::get('/attempt/{attempt}/download-word', [StudentController::class, 'downloadResultWord'])->name('download-result-word');
    });


    // Teacher Score Entry
Route::get('/teacher/scores', [TeacherScoreController::class, 'dashboard'])->name('teacher.scores.dashboard');
Route::get('/teacher/scores/select', [TeacherScoreController::class, 'selectClassSubject'])->name('teacher.scores.select');
Route::post('/teacher/scores/enter', [TeacherScoreController::class, 'enterScores'])->name('teacher.scores.enter');
Route::post('/teacher/scores/save', [TeacherScoreController::class, 'saveScores'])->name('teacher.scores.save');
Route::post('/teacher/scores/submit', [TeacherScoreController::class, 'submitScores'])->name('teacher.scores.submit');
Route::get('/teacher/scores/my-scores', [TeacherScoreController::class, 'myScores'])->name('teacher.scores.my-scores');

    Route::prefix('teacher/blog')->name('teacher.blog.')->middleware('role:teacher')->group(function () {
        Route::get('/', [BlogPostController::class, 'teacherIndex'])->name('index');
        Route::get('/create', [BlogPostController::class, 'teacherCreate'])->name('create');
        Route::post('/', [BlogPostController::class, 'teacherStore'])->name('store');
        Route::get('/{post}/edit', [BlogPostController::class, 'teacherEdit'])->name('edit');
        Route::put('/{post}', [BlogPostController::class, 'teacherUpdate'])->name('update');
    });

    // Nigerian Report Cards
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/admin/report-cards', [NigerianReportCardController::class, 'index'])->name('admin.report-cards');
        Route::get('/admin/report-cards/manual', [NigerianReportCardController::class, 'manual'])->name('admin.report-cards.manual');
        Route::post('/admin/report-cards/manual', [NigerianReportCardController::class, 'storeManual'])->name('admin.report-cards.manual.store');
        Route::get('/admin/report-cards/generate/{student}', [NigerianReportCardController::class, 'generate'])->name('admin.report-cards.generate');
        Route::get('/admin/report-cards/{id}/preview', [NigerianReportCardController::class, 'preview'])->name('admin.report-cards.preview');
        Route::get('/admin/report-cards/{id}/download', [NigerianReportCardController::class, 'downloadPDF'])->name('admin.report-cards.download');
        Route::post('/admin/report-cards/bulk', [NigerianReportCardController::class, 'bulkGenerate'])->name('admin.report-cards.bulk');
        Route::put('/admin/report-cards/{id}', [NigerianReportCardController::class, 'update'])->name('admin.report-cards.update');
        Route::put('/admin/report-cards/{id}/publication', [NigerianReportCardController::class, 'updatePublication'])->name('admin.report-cards.publication');
    });



    Route::prefix('admin/blog')->name('admin.blog.')->middleware('blog.studio')->group(function () {
        Route::get('/', [BlogPostController::class, 'adminIndex'])->name('index');
        Route::get('/create', [BlogPostController::class, 'adminCreate'])->name('create');
        Route::post('/', [BlogPostController::class, 'adminStore'])->name('store');
        Route::get('/{post}/edit', [BlogPostController::class, 'adminEdit'])->name('edit');
        Route::put('/{post}', [BlogPostController::class, 'adminUpdate'])->name('update');
        Route::delete('/{post}', [BlogPostController::class, 'adminDestroy'])->name('destroy');
    });

    // Admin/Teacher routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin,teacher')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/messages', [MessageController::class, 'adminIndex'])->name('messages.index');
        Route::post('/messages', [MessageController::class, 'adminStore'])->name('messages.store');
        
        // Admin-only routes
        Route::middleware('role:admin')->group(function () {
            // Teacher Management
            Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers');
            Route::get('/teachers/create', [AdminController::class, 'createTeacher'])->name('teacher.create');
            Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('teacher.store');
            Route::get('/teachers/{teacher}/edit', [AdminController::class, 'editTeacher'])->name('teacher.edit');
            Route::put('/teachers/{teacher}', [AdminController::class, 'updateTeacher'])->name('teacher.update');
            Route::delete('/teachers/{teacher}', [AdminController::class, 'deleteTeacher'])->name('teacher.delete');
            Route::get('/teachers/{teacher}/classes', [AdminController::class, 'assignTeacherClasses'])->name('teacher.assign-classes');
            Route::put('/teachers/{teacher}/classes', [AdminController::class, 'updateTeacherClasses'])->name('teacher.update-classes');
            
            // Student Management
            Route::get('/students', [AdminController::class, 'students'])->name('students');
            Route::get('/students/create', [AdminController::class, 'createStudent'])->name('student.create');
            Route::post('/students', [AdminController::class, 'storeStudent'])->name('student.store');
            Route::get('/students/{student}/edit', [AdminController::class, 'editStudent'])->name('student.edit');
            Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('student.update');
            Route::delete('/students/{student}', [AdminController::class, 'deleteStudent'])->name('student.delete');
            
            // Class Management
            Route::get('/classes', [AdminController::class, 'classes'])->name('classes');
            Route::post('/classes', [AdminController::class, 'storeClass'])->name('class.store');
            Route::get('/classes/{class}/edit', [AdminController::class, 'editClass'])->name('class.edit');
            Route::put('/classes/{class}', [AdminController::class, 'updateClass'])->name('class.update');
            Route::delete('/classes/{class}', [AdminController::class, 'deleteClass'])->name('class.delete');

            // Subjects Management
            Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
            Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
            Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
            Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
            Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
            Route::get('/subjects/{subject}/teachers', [SubjectController::class, 'assignTeachers'])->name('subjects.assign-teachers');
            Route::put('/subjects/{subject}/teachers', [SubjectController::class, 'updateTeachers'])->name('subjects.update-teachers');
            Route::get('/teachers/{teacher}/subjects', [SubjectController::class, 'assignSubjects'])->name('subjects.assign-subjects');
            Route::put('/teachers/{teacher}/subjects', [SubjectController::class, 'updateSubjects'])->name('subjects.update-subjects');

            // Form Teacher Management
            Route::get('/form-teachers', [FormTeacherController::class, 'index'])->name('form-teachers.index');
            Route::get('/form-teachers/create', [FormTeacherController::class, 'create'])->name('form-teachers.create');
            Route::post('/form-teachers', [FormTeacherController::class, 'store'])->name('form-teachers.store');
            Route::get('/form-teachers/{formTeacher}', [FormTeacherController::class, 'show'])->name('form-teachers.show');
            Route::get('/form-teachers/{formTeacher}/edit', [FormTeacherController::class, 'edit'])->name('form-teachers.edit');
            Route::put('/form-teachers/{formTeacher}', [FormTeacherController::class, 'update'])->name('form-teachers.update');
            Route::delete('/form-teachers/{formTeacher}', [FormTeacherController::class, 'destroy'])->name('form-teachers.destroy');

            Route::get('/enquiries', [AdminAdmissionEnquiryController::class, 'index'])->name('enquiries.index');
            Route::get('/enquiries/{enquiry}', [AdminAdmissionEnquiryController::class, 'show'])->name('enquiries.show');
            Route::put('/enquiries/{enquiry}', [AdminAdmissionEnquiryController::class, 'update'])->name('enquiries.update');

            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
            Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

            Route::get('/blog-managers', [AdminController::class, 'blogManagers'])->name('blog-managers.index');
            Route::get('/blog-managers/create', [AdminController::class, 'createBlogManager'])->name('blog-managers.create');
            Route::post('/blog-managers', [AdminController::class, 'storeBlogManager'])->name('blog-managers.store');
            Route::put('/blog-managers/{manager}/revoke', [AdminController::class, 'revokeBlogManager'])->name('blog-managers.revoke');
            Route::delete('/blog-managers/{manager}', [AdminController::class, 'deleteBlogManager'])->name('blog-managers.destroy');

            Route::get('/parents', [AdminParentController::class, 'index'])->name('parents.index');
            Route::post('/parents', [AdminParentController::class, 'store'])->name('parents.store');
            Route::post('/parents/{parent}/children', [AdminParentController::class, 'attachStudent'])->name('parents.children.attach');
            Route::delete('/parents/{parent}/children/{student}', [AdminParentController::class, 'detachStudent'])->name('parents.children.detach');

            Route::get('/fee-clearances', [AdminFeeClearanceController::class, 'index'])->name('fee-clearances.index');
            Route::put('/fee-clearances/{student}', [AdminFeeClearanceController::class, 'update'])->name('fee-clearances.update');
        });
        
        // Exams (accessible by admin and teachers)
        Route::get('/exams', [AdminController::class, 'exams'])->name('exams');
        Route::get('/exams/create', [AdminController::class, 'createExam'])->name('exam.create');
        Route::post('/exams', [AdminController::class, 'storeExam'])->name('exam.store');
        Route::get('/exams/{exam}/edit', [AdminController::class, 'editExam'])->name('exam.edit');
        Route::put('/exams/{exam}', [AdminController::class, 'updateExam'])->name('exam.update');
        Route::delete('/exams/{exam}', [AdminController::class, 'deleteExam'])->name('exam.delete');
        Route::get('/exams/{exam}/questions', [AdminController::class, 'examQuestions'])->name('exam.questions');
        Route::post('/exams/{exam}/questions', [AdminController::class, 'storeQuestion'])->name('exam.question.store');
        Route::post('/exams/{exam}/passages', [AdminController::class, 'storeQuestionPassage'])->name('exam.passage.store');
        Route::put('/passages/{passage}', [AdminController::class, 'updateQuestionPassage'])->name('exam.passage.update');
        Route::delete('/passages/{passage}', [AdminController::class, 'deleteQuestionPassage'])->name('exam.passage.delete');
        Route::get('/questions/{question}/edit', [AdminController::class, 'editQuestion'])->name('question.edit');
        Route::put('/questions/{question}', [AdminController::class, 'updateQuestion'])->name('question.update');
        Route::delete('/questions/{question}', [AdminController::class, 'deleteQuestion'])->name('question.delete');

        // Learning Sessions
        Route::get('/learning-sessions', [LearningSessionController::class, 'index'])->name('learning-sessions.index');
        Route::get('/learning-sessions/create', [LearningSessionController::class, 'create'])->name('learning-sessions.create');
        Route::post('/learning-sessions', [LearningSessionController::class, 'store'])->name('learning-sessions.store');
        Route::get('/learning-sessions/{learningSession}/edit', [LearningSessionController::class, 'edit'])->name('learning-sessions.edit');
        Route::put('/learning-sessions/{learningSession}', [LearningSessionController::class, 'update'])->name('learning-sessions.update');
        Route::delete('/learning-sessions/{learningSession}', [LearningSessionController::class, 'destroy'])->name('learning-sessions.destroy');
        Route::post('/learning-sessions/{learningSession}/questions', [LearningSessionController::class, 'storeQuestion'])->name('learning-sessions.questions.store');
        Route::delete('/learning-questions/{question}', [LearningSessionController::class, 'destroyQuestion'])->name('learning-sessions.questions.destroy');
        
        // Results & Grading
        Route::get('/exams/{exam}/results', [AdminController::class, 'examResults'])->name('exam.results');
        Route::put('/exams/{exam}/result-release', [AdminController::class, 'updateResultRelease'])->name('exam.result-release');
        Route::get('/attempts/{attempt}/grade', [AdminController::class, 'gradeAttempt'])->name('attempt.grade');
        Route::post('/attempts/{attempt}/grade', [AdminController::class, 'updateGrading'])->name('attempt.update-grade');
        
        // Exports
        Route::get('/exams/{exam}/export/pdf', [AdminController::class, 'exportResultsPDF'])->name('exam.export.pdf');
        Route::get('/exams/{exam}/export/word', [AdminController::class, 'exportResultsWord'])->name('exam.export.word');
        Route::get('/attempts/{attempt}/print', [AdminController::class, 'printScript'])->name('attempt.print');
        
        // Results Portal Routes
        Route::prefix('results')->name('results.')->withoutMiddleware('verified')->group(function () {
            Route::get('/', [ResultsController::class, 'index'])->name('index');
            Route::get('/statistics', [ResultsController::class, 'statistics'])->name('statistics');
            Route::get('/exam/{exam}', [ResultsController::class, 'examWise'])->name('exam-wise');
            Route::get('/class/{class}', [ResultsController::class, 'classWise'])->name('class-wise');
            Route::get('/student/{student}', [ResultsController::class, 'studentResults'])->name('student');
            Route::get('/export/pdf', [ResultsController::class, 'exportPDF'])->name('export-pdf');
            Route::get('/export/csv', [ResultsController::class, 'exportCSV'])->name('export-csv');
        });
    });

    // Form Teacher routes (for teachers) - FormTeacherController was renamed to TeacherScoreController
    // These routes are commented out because the methods don't exist in TeacherScoreController
    // TODO: Implement these methods or create a new FormTeacherController
    // Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
    //     Route::get('/form-teacher/dashboard', [FormTeacherController::class, 'dashboard'])->name('form-teacher.dashboard');
    //     Route::get('/form-teacher/class/{class}/results', [FormTeacherController::class, 'classResults'])->name('form-teacher.class-results');
    //     Route::get('/form-teacher/class/{class}/export', [FormTeacherController::class, 'exportResults'])->name('form-teacher.export-results');
    //     
    //     // Form Teacher - Add Students
    //     Route::get('/form-teacher/class/{class}/students/add', [FormTeacherController::class, 'showAddStudents'])->name('form-teacher.add-students');
    //     Route::post('/form-teacher/class/{class}/students', [FormTeacherController::class, 'storeStudentInClass'])->name('form-teacher.store-student');
    //     Route::delete('/form-teacher/class/{class}/students/{student}', [FormTeacherController::class, 'removeStudentFromClass'])->name('form-teacher.remove-student');
    //     
    //     // Form Teacher - Compile Results
    //     Route::get('/form-teacher/class/{class}/compile-results', [FormTeacherController::class, 'compileResults'])->name('form-teacher.compile-results');
    //     Route::get('/form-teacher/class/{class}/compile-results/form', [FormTeacherController::class, 'showCompileForm'])->name('form-teacher.compile-form');
    //     Route::post('/form-teacher/class/{class}/compile-results', [FormTeacherController::class, 'storeCompiledResults'])->name('form-teacher.store-compiled-results');
    //     
    //     // Form Teacher - Report Cards
    //     Route::get('/report-cards', [FormTeacherController::class, 'reportCards'])->name('report-cards');
    //     Route::get('/report-cards/create', [FormTeacherController::class, 'showReportCardForm'])->name('report-card.create');
    //     Route::get('/report-cards/student/{student}', [FormTeacherController::class, 'showReportCardForm'])->name('report-card.edit');
    //     Route::post('/report-cards', [FormTeacherController::class, 'storeReportCard'])->name('report-card.store');
    //     Route::get('/report-cards/{reportCard}/pdf', [FormTeacherController::class, 'generateReportCardPDF'])->name('report-card.pdf');
    //     Route::delete('/report-cards/{reportCard}', [FormTeacherController::class, 'deleteReportCard'])->name('report-card.delete');
    // });
});

// Temporary route to fix exam total marks
Route::get('/fix-exam-totals', function() {
    $exams = \App\Models\Exam::all();
    $fixed = 0;
    $report = '<h1>Exam Totals Fix Report</h1><table border="1" style="border-collapse:collapse; padding:10px;"><tr><th>Exam</th><th>Old Total</th><th>New Total</th><th>Status</th></tr>';
    
    foreach ($exams as $exam) {
        $oldTotal = $exam->total_marks;
        $correctTotal = $exam->questions()->sum('marks');
        
        if ($oldTotal != $correctTotal) {
            $exam->update(['total_marks' => $correctTotal]);
            $report .= "<tr><td>{$exam->title}</td><td style='color:red'>{$oldTotal}</td><td style='color:green'>{$correctTotal}</td><td>✅ Fixed</td></tr>";
            $fixed++;
        } else {
            $report .= "<tr><td>{$exam->title}</td><td>{$oldTotal}</td><td>{$correctTotal}</td><td>✓ Already correct</td></tr>";
        }
    }
    
    $report .= '</table><br><h2 style="color:green">✅ Fixed ' . $fixed . ' exam(s)!</h2>';
    $report .= '<br><a href="/">Go to Homepage</a>';
    
    return $report;
});
