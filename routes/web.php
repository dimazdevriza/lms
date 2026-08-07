<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Guru\AssignmentController;
use App\Http\Controllers\Guru\AttendanceController;
use App\Http\Controllers\Guru\ClassroomController;
use App\Http\Controllers\Guru\MaterialController;
use App\Http\Controllers\Guru\MeetingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\StudentAttendanceController;
use App\Http\Controllers\Siswa\StudentDashboardController;
use App\Http\Controllers\Siswa\StudentMaterialController;
use App\Http\Controllers\Siswa\StudentSubmissionController;
use App\Http\Controllers\TataUsaha\ReportController;
use App\Http\Controllers\TataUsaha\StudentController;
use App\Http\Controllers\TataUsaha\TeacherController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\TataUsaha\TeachingAssignmentController;
use App\Http\Controllers\TataUsaha\ScheduleController;
use App\Http\Controllers\Admin\ImpersonationController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'tatausaha' => redirect()->route('tatausaha.dashboard'),
        'guru' => redirect()->route('guru.dashboard'),
        'siswa' => redirect()->route('siswa.dashboard'),
        default => redirect()->route('siswa.dashboard'),
    };
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'tatausaha' => redirect()->route('tatausaha.dashboard'),
        'guru' => redirect()->route('guru.dashboard'),
        'siswa' => redirect()->route('siswa.dashboard'),
        default => redirect()->route('siswa.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    // NOTIFICATIONS
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/poll', [App\Http\Controllers\NotificationController::class, 'poll'])->name('notifications.poll');

    // WEB PUSH SUBSCRIPTIONS
    Route::post('/push/subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

    // IMPERSONATION STOP
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

    // ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('impersonate/start/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::resource('academic-years', AcademicYearController::class)->except(['show']);
        Route::resource('classes', AdminClassController::class);
        Route::resource('subjects', SubjectController::class);

        // PRESENSI ADMIN & KELOLA PERTEMUAN
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AdminAttendanceController::class, 'index'])->name('index');
            Route::get('/classes/{class}', [AdminAttendanceController::class, 'showClass'])->name('showClass');
            Route::get('/classes/{class}/subjects/{subject}', [AdminAttendanceController::class, 'showSubject'])->name('showSubject');
            Route::get('/meetings/{meeting}/materials', [AdminAttendanceController::class, 'meetingMaterials'])->name('meetingMaterials');
            Route::get('/meetings/{meeting}/assignments', [AdminAttendanceController::class, 'meetingAssignments'])->name('meetingAssignments');
            Route::get('/meetings/{meeting}/edit', [AdminAttendanceController::class, 'editMeetingAttendance'])->name('editMeeting');
            Route::post('/meetings/{meeting}', [AdminAttendanceController::class, 'updateMeetingAttendance'])->name('updateMeeting');
        });

        Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    });

    // TATA USAHA
    Route::middleware('role:tatausaha')->prefix('tata-usaha')->name('tatausaha.')->group(function () {
        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);

        Route::get('teaching-assignments/print', [TeachingAssignmentController::class, 'printPdf'])->name('teaching-assignments.print');
        Route::resource('teaching-assignments', TeachingAssignmentController::class)->except(['show'])->parameters([
            'teaching-assignments' => 'teaching_assignment',
        ]);
        // Jadwal Pelajaran
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::get('/time-slots', [ScheduleController::class, 'timeSlots'])->name('time-slots');
            Route::post('/time-slots', [ScheduleController::class, 'storeTimeSlot'])->name('time-slots.store');
            Route::delete('/time-slots/{timeSlot}', [ScheduleController::class, 'deleteTimeSlot'])->name('time-slots.destroy');
            Route::get('/{schoolClass}/edit', [ScheduleController::class, 'edit'])->name('edit');
            Route::put('/{schoolClass}', [ScheduleController::class, 'update'])->name('update');
            Route::delete('/{schoolClass}/clear', [ScheduleController::class, 'clear'])->name('clear');
            Route::get('/print', [ScheduleController::class, 'printSchedule'])->name('print');
        });

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/print', [ReportController::class, 'print'])->name('reports.print');
    });

    // GURU & ADMIN
    Route::middleware('role:guru,admin')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [MaterialController::class, 'dashboard'])->name('dashboard');
        Route::resource('materials', MaterialController::class);
        Route::resource('meetings', MeetingController::class);
        Route::get('meetings/class/{classSlug}/{subjectSlug}', [MeetingController::class, 'classMeetingsIndex'])->name('meetings.class-meetings');
        Route::get('meetings/class/{classSlug}/{subjectSlug}/create', [MeetingController::class, 'classMeetingsCreate'])->name('meetings.class-meetings.create');
        Route::post('meetings/{meeting}/video-link', [MeetingController::class, 'updateVideoLink'])->name('meetings.updateVideoLink');
        Route::post('meetings/{meeting}/toggle-visibility', [MeetingController::class, 'toggleVisibility'])->name('meetings.toggleVisibility');
        Route::get('assignments/grading', [AssignmentController::class, 'grading'])->name('assignments.grading');
        Route::resource('assignments', AssignmentController::class);
        Route::post('assignments/answers/{answer}/grade', [AssignmentController::class, 'gradeQuestion'])->name('assignments.grade-question');
        Route::post('assignments/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('assignments.grade-submission');
        Route::post('assignments/{assignment}/extend-deadline', [AssignmentController::class, 'extendDeadline'])->name('assignments.extend-deadline');
        Route::resource('attendances', AttendanceController::class);

        // WALI KELAS - Classroom Management
        Route::prefix('classroom')->name('classroom.')->group(function () {
            Route::get('/', [ClassroomController::class, 'index'])->name('index');
            Route::get('/{class}', [ClassroomController::class, 'show'])->name('show');

            // Absensi Harian Kelas
            Route::get('/{class}/attendance', [ClassroomController::class, 'attendance'])->name('attendance');
            Route::get('/{class}/attendance/create', [ClassroomController::class, 'attendanceCreate'])->name('attendance.create');
            Route::post('/{class}/attendance', [ClassroomController::class, 'attendanceStore'])->name('attendance.store');
            Route::get('/{class}/attendance/{attendance}', [ClassroomController::class, 'attendanceShow'])->name('attendance.show');

            // Catatan Perilaku
            Route::get('/{class}/behavior', [ClassroomController::class, 'behavior'])->name('behavior');
            Route::get('/{class}/behavior/create', [ClassroomController::class, 'behaviorCreate'])->name('behavior.create');
            Route::post('/{class}/behavior', [ClassroomController::class, 'behaviorStore'])->name('behavior.store');
            Route::delete('/{class}/behavior/{behavior}', [ClassroomController::class, 'behaviorDestroy'])->name('behavior.destroy');

            // Rekap Nilai
            Route::get('/{class}/grades', [ClassroomController::class, 'grades'])->name('grades');
            Route::get('/{class}/grades/input', [ClassroomController::class, 'gradesInput'])->name('grades.input');
            Route::post('/{class}/grades', [ClassroomController::class, 'gradesStore'])->name('grades.store');
        });
    });

    // SISWA
    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/directory', [StudentDashboardController::class, 'directory'])->name('directory');
        
        // Flow Baru: Mapel -> Pertemuan -> Detail
        Route::get('/subjects', [StudentMaterialController::class, 'subjects'])->name('subjects.index');
        Route::get('/subjects/{subject}', [StudentMaterialController::class, 'subjectMeetings'])->name('subjects.show');
        Route::get('/meetings/{meeting}', [StudentMaterialController::class, 'meetingDetail'])->name('meetings.show');
        Route::get('/materials/{material}', [StudentMaterialController::class, 'show'])->name('materials.show');
        
        Route::get('/assignments', [StudentSubmissionController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [StudentSubmissionController::class, 'show'])->name('assignments.show');
        Route::post('/assignments/{assignment}/submit', [StudentSubmissionController::class, 'store'])->name('assignments.submit');
        Route::post('/assignments/{assignment}/unsubmit', [StudentSubmissionController::class, 'unsubmit'])->name('assignments.unsubmit');
        Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');
    });

    // FORUM / STREAM
    Route::get('/forum/{classSlug}/{subjectSlug}', [App\Http\Controllers\ForumController::class, 'index'])->name('forum.index');
    Route::post('/forum/{classSlug}/{subjectSlug}/post', [App\Http\Controllers\ForumController::class, 'storePost'])->name('forum.post.store');
    Route::post('/forum/post/{post}/comment', [App\Http\Controllers\ForumController::class, 'storeComment'])->name('forum.comment.store');
    Route::delete('/forum/post/{post}', [App\Http\Controllers\ForumController::class, 'destroyPost'])->name('forum.post.destroy');

    // MEETING DISCUSSION FORUM
    Route::post('/meetings/{meeting}/discussion', [App\Http\Controllers\ForumController::class, 'storeMeetingPost'])->name('meeting.discussion.store');
    Route::post('/meetings/discussion/{post}/comment', [App\Http\Controllers\ForumController::class, 'storeMeetingComment'])->name('meeting.discussion.comment');
    Route::delete('/meetings/discussion/{post}', [App\Http\Controllers\ForumController::class, 'destroyMeetingPost'])->name('meeting.discussion.destroy');
    Route::delete('/forum/comment/{comment}', [App\Http\Controllers\ForumController::class, 'destroyComment'])->name('forum.comment.destroy');

    // SUBMISSION COMMENTS
    Route::post('/submissions/{submission}/comments', [App\Http\Controllers\SubmissionCommentController::class, 'store'])->name('submissions.comments.store');

    // SECURE FILE DOWNLOADS
    Route::get('/assignments/{assignment}/download-file', [App\Http\Controllers\FileDownloadController::class, 'downloadAssignment'])->name('assignments.download');
    Route::get('/submissions/{submission}/download-file', [App\Http\Controllers\FileDownloadController::class, 'downloadSubmission'])->name('submissions.download');
    Route::get('/materials/{material}/view-file', [App\Http\Controllers\FileDownloadController::class, 'downloadMaterial'])->name('materials.view-file');
});

// PARENT MONITORING PORTAL
Route::prefix('ortu')->name('parent.')->group(function () {
    Route::get('/', [App\Http\Controllers\ParentController::class, 'index'])->name('index');
    Route::post('/access', [App\Http\Controllers\ParentController::class, 'access'])->middleware('throttle:parent-access')->name('access');
    Route::get('/dashboard', [App\Http\Controllers\ParentController::class, 'dashboard'])->name('dashboard');
    Route::get('/view/{code}', [App\Http\Controllers\ParentController::class, 'viewDirect'])->middleware('throttle:parent-direct')->name('view');
    Route::post('/view/confirm', [App\Http\Controllers\ParentController::class, 'viewDirectConfirm'])->middleware('throttle:parent-confirm')->name('view.confirm');
    Route::post('/logout', [App\Http\Controllers\ParentController::class, 'logout'])->name('logout');
});

// Parent code regeneration (authenticated users only)
Route::post('/parent-code/{student}/regenerate', [App\Http\Controllers\ParentController::class, 'regenerateCode'])
    ->middleware(['auth'])
    ->name('parent.code.regenerate');

Route::post('/parent-code/{student}/reveal', [App\Http\Controllers\ParentController::class, 'revealCode'])
    ->middleware(['auth', 'role:admin,guru,tatausaha'])
    ->name('parent.code.reveal');

require __DIR__.'/auth.php';




