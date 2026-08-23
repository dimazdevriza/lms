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
    private function getTeacher(): ?Teacher
    {
        return Teacher::where('user_id', auth()->id())->first();
    }

    private function canAccessHomeroom(?Teacher $teacher, SchoolClass $class): bool
    {
        if (auth()->user()?->role === 'admin' || session()->has('impersonate_original_id')) {
            return true;
        }

        return $teacher && $class->homeroom_teacher_id === $teacher->id;
    }

    private function isTeacherOfClass(?Teacher $teacher, SchoolClass $class): bool
    {
        if (auth()->user()?->role === 'admin' || session()->has('impersonate_original_id')) {
            return true;
        }

        if (! $teacher) {
            return false;
        }

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

        if ($teacher) {
            $homeroomClasses = $teacher->homeroomClasses()->get();
            $classes = $homeroomClasses;

            $assignedClassIds = \App\Models\ClassSubjectTeacher::where('teacher_id', $teacher->id)->pluck('class_id');
            $scheduleClassIds = \App\Models\Schedule::where('teacher_id', $teacher->id)->pluck('class_id');
            $meetingClassIds = \App\Models\Meeting::where('teacher_id', $teacher->id)->pluck('class_id');

            $allTaughtClassIds = $assignedClassIds->concat($scheduleClassIds)->concat($meetingClassIds)->unique();
            $homeroomClassIds = $homeroomClasses->pluck('id');
            $teachingOnlyClassIds = $allTaughtClassIds->diff($homeroomClassIds);

            $teachingClasses = SchoolClass::whereIn('id', $teachingOnlyClassIds)->withCount('students')->orderBy('name')->get();
        } else {
            $homeroomClasses = SchoolClass::withCount('students')->orderBy('name')->get();
            $classes = $homeroomClasses;
            $teachingClasses = collect([]);
        }

        return view('guru.classroom.index', compact('classes', 'homeroomClasses', 'teachingClasses'));
    }

    // DETAIL KELAS DAN DATA SISWA
    public function show(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();

        if (! $this->isTeacherOfClass($teacher, $class)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat data siswa kelas ini.');
        }

        $isHomeroomTeacher = $this->canAccessHomeroom($teacher, $class);
        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        $studentIds = $students->pluck('id');

        // Akumulasi Kehadiran Kelas (Mata Pelajaran + Harian)
        $subjectHadirCount = \App\Models\AttendanceDetail::whereIn('student_id', $studentIds)
            ->whereHas('attendance', fn($q) => $q->where('class_id', $class->id))
            ->whereIn('status', ['hadir'])->count();

        $subjectTotalCount = \App\Models\AttendanceDetail::whereIn('student_id', $studentIds)
            ->whereHas('attendance', fn($q) => $q->where('class_id', $class->id))->count();

        $dailyHadirCount = \App\Models\ClassAttendanceDetail::whereIn('student_id', $studentIds)
            ->whereHas('classAttendance', fn($q) => $q->where('class_id', $class->id))
            ->where('status', 'hadir')->count();

        $dailyTotalCount = \App\Models\ClassAttendanceDetail::whereIn('student_id', $studentIds)
            ->whereHas('classAttendance', fn($q) => $q->where('class_id', $class->id))->count();

        $totalHadir = $subjectHadirCount + $dailyHadirCount;
        $totalPresensi = $subjectTotalCount + $dailyTotalCount;
        $classAttendanceAvg = $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100, 1) : 100;

        // Akumulasi Nilai Kelas (Tugas All Mapel & Evaluasi/Rapor)
        $tugasScores = \App\Models\AssignmentSubmission::whereIn('student_id', $studentIds)
            ->whereNotNull('score')
            ->whereHas('assignment', fn($q) => $q->where('class_id', $class->id))
            ->pluck('score');

        $evalScores = \App\Models\StudentGrade::whereIn('student_id', $studentIds)
            ->where('class_id', $class->id)
            ->pluck('score');

        $allClassScores = $tugasScores->concat($evalScores);
        $classGradeAvg = $allClassScores->count() > 0 ? round($allClassScores->avg(), 1) : null;

        // Catatan Perilaku Stats
        $behaviorCountPositif = \App\Models\BehaviorRecord::where('class_id', $class->id)->where('type', 'positif')->count();
        $behaviorCountNegatif = \App\Models\BehaviorRecord::where('class_id', $class->id)->where('type', 'negatif')->count();

        return view('guru.classroom.show', compact(
            'class', 'students', 'isHomeroomTeacher',
            'classAttendanceAvg', 'classGradeAvg', 'behaviorCountPositif', 'behaviorCountNegatif'
        ));
    }

    // ABSENSI KELAS
    public function attendance(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if (! $this->canAccessHomeroom($teacher, $class)) {
            abort(403, 'Unauthorized');
        }

        $attendances = $class->classAttendances()->orderByDesc('date')->paginate(10);
        $students = $class->students()->with('user')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        $studentIds = $students->pluck('id');

        // Presensi per mata pelajaran (dengan fallback meeting.class_id)
        $subjectAttendanceRaw = \App\Models\AttendanceDetail::whereIn('student_id', $studentIds)
            ->whereHas('attendance', function ($q) use ($class) {
                $q->where('class_id', $class->id)
                  ->orWhereHas('meeting', fn($mq) => $mq->where('class_id', $class->id));
            })
            ->with(['attendance.subject'])
            ->get();

        // Presensi harian wali kelas
        $dailyAttendanceStats = \App\Models\ClassAttendanceDetail::whereIn('student_id', $studentIds)
            ->whereHas('classAttendance', function ($q) use ($class) {
                $q->where('class_id', $class->id);
            })
            ->select('student_id', 'status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('student_id', 'status')
            ->get();

        $accumulatedAttendance = [];
        foreach ($students as $student) {
            $sSubjDetails = $subjectAttendanceRaw->where('student_id', $student->id);
            $sDaily = $dailyAttendanceStats->where('student_id', $student->id);

            $subjHadir = $sSubjDetails->whereIn('status', ['hadir'])->count();
            $subjIzin = $sSubjDetails->where('status', 'izin')->count();
            $subjSakit = $sSubjDetails->where('status', 'sakit')->count();
            $subjAlpa = $sSubjDetails->whereIn('status', ['alpa', 'cabut'])->count();

            $dailyHadir = $sDaily->where('status', 'hadir')->sum('count');
            $dailyIzin = $sDaily->where('status', 'izin')->sum('count');
            $dailySakit = $sDaily->where('status', 'sakit')->sum('count');
            $dailyAlpa = $sDaily->whereIn('status', ['alpa', 'cabut'])->sum('count');

            $hadir = $subjHadir + $dailyHadir;
            $izin = $subjIzin + $dailyIzin;
            $sakit = $subjSakit + $dailySakit;
            $alpa = $subjAlpa + $dailyAlpa;
            $total = $hadir + $izin + $sakit + $alpa;
            $percentage = $total > 0 ? round(($hadir / $total) * 100, 1) : 100;

            // Breakdown per mata pelajaran
            $subjectBreakdown = [];
            $groupedBySubj = $sSubjDetails->groupBy(fn($item) => $item->attendance?->subject?->name ?? 'Lainnya');
            foreach ($groupedBySubj as $subjectName => $records) {
                $subHadir = $records->whereIn('status', ['hadir'])->count();
                $subIzin = $records->where('status', 'izin')->count();
                $subSakit = $records->where('status', 'sakit')->count();
                $subAlpa = $records->whereIn('status', ['alpa', 'cabut'])->count();
                $subTotal = $records->count();
                $subPct = $subTotal > 0 ? round(($subHadir / $subTotal) * 100, 1) : 100;

                $subjectBreakdown[$subjectName] = [
                    'hadir' => $subHadir,
                    'izin' => $subIzin,
                    'sakit' => $subSakit,
                    'alpa' => $subAlpa,
                    'total' => $subTotal,
                    'percentage' => $subPct,
                ];
            }

            $accumulatedAttendance[$student->id] = [
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total' => $total,
                'percentage' => $percentage,
                'subject_breakdown' => $subjectBreakdown,
            ];
        }

        return view('guru.classroom.attendance.index', compact('class', 'attendances', 'students', 'accumulatedAttendance'));
    }

    public function attendanceCreate(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
        if (! $this->canAccessHomeroom($teacher, $class) || $attendance->class_id !== $class->id) {
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
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
        if (! $this->canAccessHomeroom($teacher, $class) || $behavior->class_id !== $class->id) {
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
        if (! $this->canAccessHomeroom($teacher, $class)) {
            abort(403, 'Unauthorized');
        }

        $students = $class->students()->with('user', 'grades')->get()
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)
            ->values();

        $studentIds = $students->pluck('id');

        // Nilai Tugas dari semua mapel
        $assignmentSubmissions = \App\Models\AssignmentSubmission::whereIn('student_id', $studentIds)
            ->whereNotNull('score')
            ->whereHas('assignment', function ($q) use ($class) {
                $q->where('class_id', $class->id);
            })
            ->with('assignment.subject')
            ->get();

        // Nilai Evaluasi/Rapor
        $evalGrades = \App\Models\StudentGrade::whereIn('student_id', $studentIds)
            ->where('class_id', $class->id)
            ->with('subject')
            ->get();

        // Ambil daftar mata pelajaran di kelas ini
        $subjectIds = $assignmentSubmissions->pluck('assignment.subject_id')
            ->concat($evalGrades->pluck('subject_id'))
            ->filter()
            ->unique();

        $subjects = \App\Models\Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::orderBy('name')->get();
        }

        $accumulatedGrades = [];
        foreach ($students as $student) {
            $sSubmissions = $assignmentSubmissions->where('student_id', $student->id);
            $sEval = $evalGrades->where('student_id', $student->id);

            $tugasAvg = $sSubmissions->count() > 0 ? round($sSubmissions->avg('score'), 1) : null;
            $evalAvg = $sEval->count() > 0 ? round($sEval->avg('score'), 1) : null;

            $allScores = $sSubmissions->pluck('score')->concat($sEval->pluck('score'));
            $overallAvg = $allScores->count() > 0 ? round($allScores->avg(), 1) : null;

            $subjectBreakdown = [];
            foreach ($subjects as $sub) {
                $subTugas = $sSubmissions->filter(fn($subm) => $subm->assignment?->subject_id == $sub->id)->pluck('score');
                $subEval = $sEval->where('subject_id', $sub->id)->pluck('score');
                $subAll = $subTugas->concat($subEval);

                $subjectBreakdown[$sub->id] = [
                    'subject_name' => $sub->name,
                    'avg' => $subAll->count() > 0 ? round($subAll->avg(), 1) : null,
                    'count' => $subAll->count(),
                ];
            }

            $accumulatedGrades[$student->id] = [
                'tugas_avg' => $tugasAvg,
                'eval_avg' => $evalAvg,
                'overall_avg' => $overallAvg,
                'total_evaluations' => $allScores->count(),
                'subject_breakdown' => $subjectBreakdown,
            ];
        }

        return view('guru.classroom.grades.index', compact('class', 'students', 'subjects', 'accumulatedGrades'));
    }

    public function gradesInput(SchoolClass $class): View
    {
        $teacher = $this->getTeacher();
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
        if (! $this->canAccessHomeroom($teacher, $class)) {
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
