<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        // Jika ada filter class_id dan subject_id, tampilkan riwayat untuk grup tersebut
        if ($request->has('class_id') && $request->has('subject_id')) {
            $currentClass = SchoolClass::findOrFail($request->class_id);
            $currentSubject = Subject::findOrFail($request->subject_id);
            
            $records = Attendance::where('teacher_id', $teacherId)
                ->where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->with(['schoolClass', 'subject', 'meeting'])
                ->latest('date')
                ->get();

            return view('guru.attendance.list', compact('records', 'currentClass', 'currentSubject'));
        }

        // Tampilan awal: Grup Kelas & Mapel yang memiliki riwayat absensi
        $attendanceGroups = Attendance::where('teacher_id', $teacherId)
            ->with(['schoolClass', 'subject'])
            ->select('class_id', 'subject_id')
            ->selectRaw('count(*) as total_records')
            ->groupBy('class_id', 'subject_id')
            ->get()
            ->groupBy(function ($group) {
                return $group->schoolClass->major ?? 'Umum';
            })
            ->map(function ($majorGroup) {
                return $majorGroup->groupBy(function ($item) {
                    return $item->subject->name ?? 'Tanpa Mata Pelajaran';
                });
            });

        return view('guru.attendance.index', compact('attendanceGroups'));
    }

    public function create(Request $request): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();
        $classes = SchoolClass::with(['students.user'])->orderBy('name')->get();
        
        foreach ($classes as $class) {
            $sortedStudents = $class->students->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)->values();
            $class->setRelation('students', $sortedStudents);
        }

        $assignedSubjectIds = $teacher->teachingAssignments()->pluck('subject_id');
        $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        
        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('name')->get();
        }

        $meeting = null;
        $studentStats = [];
        if ($request->has('meeting_id')) {
            $meeting = \App\Models\Meeting::with(['schoolClass.students.user'])->findOrFail($request->meeting_id);
            // Ensure the meeting belongs to this teacher
            abort_unless($meeting->teacher_id == $teacher->id, 403);

            $studentIds = $meeting->schoolClass->students->pluck('id');
            $stats = \App\Models\AttendanceDetail::whereIn('student_id', $studentIds)
                ->whereHas('attendance', function($q) use ($meeting) {
                    $q->where('subject_id', $meeting->subject_id);
                })
                ->select('student_id', 'status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('student_id', 'status')
                ->get();
            
            foreach ($stats as $stat) {
                $studentStats[$stat->student_id][$stat->status] = $stat->count;
            }
        } else {
            // Jika tidak ada meeting_id, arahkan guru ke halaman pertemuan
            return redirect()->route('guru.meetings.index')
                ->with('error', 'Silakan pilih pertemuan terlebih dahulu untuk melakukan presensi.');
        }

        return view('guru.attendance.create', compact('classes', 'subjects', 'meeting', 'studentStats'));
    }

    public function show(Attendance $attendance): View
    {
        abort_unless($attendance->teacher_id == Teacher::where('user_id', Auth::id())->value('id'), 403);

        $attendance->load(['schoolClass', 'subject', 'details.student.user']);

        $sortedDetails = $attendance->details->sortBy(fn($d) => strtolower($d->student->user?->name ?? ''), SORT_NATURAL)->values();
        $attendance->setRelation('details', $sortedDetails);

        return view('guru.attendance.show', compact('attendance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'meeting_id' => ['required', 'exists:meetings,id'],
            'date' => ['required', 'date'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['nullable', 'in:hadir,izin,sakit,alpa,cabut'],
        ]);
 
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);
 
        $attendance = Attendance::updateOrCreate(
            [
                'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'meeting_id' => $data['meeting_id'] ?? null,
            ],
            [
                'teacher_id' => $teacherId,
                'date' => $data['date'],
                'submitted_at' => now(),
            ]
        );
 
        $students = Student::where('class_id', $data['class_id'])->pluck('id');
        $statuses = $data['statuses'] ?? [];
 
        foreach ($students as $studentId) {
            $status = $statuses[$studentId] ?? 'hadir';
            AttendanceDetail::create([
                'attendance_id' => $attendance->id,
                'student_id' => (int) $studentId,
                'status' => $status,
            ]);
        }
 
        if ($data['meeting_id'] ?? null) {
            return redirect()->route('guru.meetings.show', $data['meeting_id'])
                ->with('success', 'Presensi berhasil disimpan.');
        }

        return redirect()->route('guru.attendances.index')
            ->with('success', 'Absensi berhasil disimpan.');
    }
    public function edit(Attendance $attendance): View
    {
        abort_unless($attendance->teacher_id == Teacher::where('user_id', Auth::id())->value('id'), 403);
        $attendance->load(['schoolClass.students.user', 'subject', 'meeting', 'details']);
        
        $classes = SchoolClass::with(['students.user'])->where('id', $attendance->class_id)->get();
        $subjects = Subject::where('id', $attendance->subject_id)->get();
        
        // Buat map status siswa
        $studentStatuses = [];
        foreach ($attendance->details as $detail) {
            $studentStatuses[$detail->student_id] = $detail->status;
        }

        return view('guru.attendance.edit', compact('attendance', 'classes', 'subjects', 'studentStatuses'));
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        abort_unless($attendance->teacher_id == Teacher::where('user_id', Auth::id())->value('id'), 403);
        
        $data = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['nullable', 'in:hadir,izin,sakit,alpa,cabut'],
        ]);

        $attendance->update(['date' => $data['date']]);

        $students = Student::where('class_id', $attendance->class_id)->pluck('id');
        $statuses = $data['statuses'] ?? [];

        foreach ($students as $studentId) {
            $status = $statuses[$studentId] ?? 'hadir';
            
            AttendanceDetail::updateOrCreate(
                ['attendance_id' => $attendance->id, 'student_id' => $studentId],
                ['status' => $status]
            );
        }

        return redirect()->route('guru.attendances.show', $attendance->id)
            ->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        abort_unless($attendance->teacher_id == Teacher::where('user_id', Auth::id())->value('id'), 403);

        $attendance->details()->delete();
        $attendance->delete();

        return redirect()->route('guru.attendances.index')
            ->with('success', 'Rekaman absensi berhasil dihapus.');
    }
}

