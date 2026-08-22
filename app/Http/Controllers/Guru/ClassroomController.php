<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\BehaviorRecord;
use App\Models\StudentGrade;
use App\Models\ClassAttendance;
use App\Models\ClassAttendanceDetail;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    private function getTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    private function isTeacherOfClass(Teacher $teacher, SchoolClass $class): bool
    {
        if ($class->homeroom_teacher_id === $teacher->id) {
            return true;
        }

        if (\App\Models\ClassSubjectTeacher::where('teacher_id', $teacher->id)->where('class_id', $class->id)->exists()) {
            return true;
        }

        if (\App\Models\Schedule::where('teacher_id', $teacher->id)->where('class_id', $class->id)->exists()) {
            return true;
        }

        if (\App\Models\Meeting::where('teacher_id', $teacher->id)->where('class_id', $class->id)->exists()) {
            return true;
        }

        return false;
    }

    // DAFTAR KELAS YANG DIAMPU SEBAGAI WALI KELAS DAN GURU MAPEL
    public function index(): View
    {
        $teacher = $this->getTeacher();
        $classes = $teacher->homeroomClasses()->get();
        $homeroomClasses = $classes;

        $assignedClassIds = \App\Models\ClassSubjectTeacher::where('teacher_id', $teacher->id)->pluck('class_id');
        $scheduleClassIds = \App\Models\Schedule::where('teacher_id', $teacher->id)->pluck('class_id');
        $meetingClassIds = \App\Models\Meeting::where('teacher_id', $teacher->id)->pluck('class_id');

        $allTaughtClassIds = $assignedClassIds->concat($scheduleClassIds)->concat($meetingClassIds)->unique();
        $homeroomClassIds = $homeroomClasses->pluck('id');
        $teachingOnlyClassIds = $allTaughtClassIds->diff($homeroomClassIds);

        $teachingClasses = SchoolClass::whereIn('id', $teachingOnlyClassIds)->withCount('students')->orderBy('name')->get();

        return view('guru.classroom.index', compact('classes', 'homeroomClasses', 'teachingClasses'));
    }

    // DETAIL KELAS DAN DATA SISWA
    public function show(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();

        if (! $this->isTeacherOfClass($teacher, $class)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat data siswa kelas ini.');
        }

        $isHomeroomTeacher = ($class->homeroom_teacher_id === $teacher->id);
        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        return view('guru.classroom.show', compact('class', 'students', 'isHomeroomTeacher'));
    }

    // ABSENSI KELAS
    public function attendance(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $attendances = $class->classAttendances()->orderByDesc('date')->paginate(10);
        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        return view('guru.classroom.attendance.index', compact('class', 'attendances', 'students'));
    }

    public function attendanceCreate(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();
        $today = now()->toDateString();

        return view('guru.classroom.attendance.create', compact('class', 'students', 'today'));
    }

    public function attendanceStore(Request $request, SchoolClass $class): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,izin,sakit,alpa,cabut'],
        ]);

        // Cek apakah absensi untuk tanggal ini sudah ada
        $attendance = ClassAttendance::firstOrCreate(
            ['class_id' => $class->id, 'date' => $data['date']]
        );

        // Update atau buat attendance details
        foreach ($data['statuses'] as $studentId => $status) {
            ClassAttendanceDetail::updateOrCreate(
                ['class_attendance_id' => $attendance->id, 'student_id' => $studentId],
                ['status' => $status]
            );
        }

        return redirect()->route('guru.classroom.attendance', $class)
            ->with('success', 'Absensi berhasil dicatat.');
    }

    public function attendanceShow(SchoolClass $class, ClassAttendance $attendance): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id || $attendance->class_id !== $class->id) {
            abort(403, 'Unauthorized');
        }

        $attendance->load(['details.student.user']);
        $sortedDetails = $attendance->details->sortBy(fn($d) => strtolower($d->student?->user?->name ?? ''), SORT_NATURAL)->values();
        $attendance->setRelation('details', $sortedDetails);

        return view('guru.classroom.attendance.show', compact('class', 'attendance'));
    }

    // CATATAN PERILAKU
    public function behavior(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();
        $behaviors = $class->behaviorRecords()->with('student.user')->orderByDesc('date')->paginate(10);

        return view('guru.classroom.behavior.index', compact('class', 'students', 'behaviors'));
    }

    public function behaviorCreate(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        return view('guru.classroom.behavior.create', compact('class', 'students'));
    }

    public function behaviorStore(Request $request, SchoolClass $class): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:positif,negatif'],
            'date' => ['required', 'date'],
        ]);

        BehaviorRecord::create([
            'class_id' => $class->id,
            ...$data
        ]);

        return redirect()->route('guru.classroom.behavior', $class)
            ->with('success', 'Catatan perilaku berhasil ditambahkan.');
    }

    public function behaviorDestroy(SchoolClass $class, BehaviorRecord $behavior): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id || $behavior->class_id !== $class->id) {
            abort(403, 'Unauthorized');
        }

        $behavior->delete();

        return redirect()->route('guru.classroom.behavior', $class)
            ->with('success', 'Catatan perilaku berhasil dihapus.');
    }

    // REKAP NILAI
    public function grades(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $students = $class->students()->with('user', 'grades')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        return view('guru.classroom.grades.index', compact('class', 'students'));
    }

    public function gradesInput(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        return view('guru.classroom.grades.input', compact('class', 'students'));
    }

    public function gradesStore(Request $request, SchoolClass $class): RedirectResponse
    {
        $teacher = $this->getTeacher();
        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.subject_id' => ['required', 'exists:subjects,id'],
            'grades.*.assessment_type' => ['required', 'string'],
            'grades.*.score' => ['required', 'integer', 'min:0', 'max:100'],
            'grades.*.assessment_date' => ['required', 'date'],
        ]);

        foreach ($data['grades'] as $grade) {
            StudentGrade::updateOrCreate(
                [
                    'student_id' => $grade['student_id'],
                    'class_id' => $class->id,
                    'subject_id' => $grade['subject_id'],
                    'assessment_type' => $grade['assessment_type'],
                    'assessment_date' => $grade['assessment_date'],
                ],
                ['score' => $grade['score']]
            );
        }

        return redirect()->route('guru.classroom.grades', $class)
            ->with('success', 'Nilai siswa berhasil disimpan.');
    }
}
