<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\Schedule;
use App\Models\AcademicYear;
use Carbon\Carbon;

class MaterialController extends Controller
{
    public function dashboard(): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->first();
        abort_unless($teacher, 403);

        $meetingsCount = \App\Models\Meeting::where('teacher_id', $teacher->id)->count();
        $materialsCount = Material::where('teacher_id', $teacher->id)->count();
        $assignmentsCount = \App\Models\Assignment::where('teacher_id', $teacher->id)->count();
        
        // Pengumpulan yang belum dinilai (score is null)
        $pendingGradesCount = \App\Models\AssignmentSubmission::whereHas('assignment', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->whereNull('score')->count();

        // Tugas terbaru yang punya submission belum dinilai (untuk panel "Perlu Dinilai")
        $recentPendingAssignments = \App\Models\Assignment::where('teacher_id', $teacher->id)
            ->whereHas('submissions', function($q) {
                $q->whereNull('score');
            })
            ->with(['submissions', 'schoolClass', 'subject'])
            ->latest()
            ->take(5)
            ->get();

        // Ambil daftar kelas dari materi/tugas yang pernah dibuat
        $assignedClasses = SchoolClass::whereHas('materials', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->orWhereHas('assignments', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->orWhereHas('studentGrades', function($q) use ($teacher) {
            // Karena student_grades tidak punya teacher_id, kita pakai subject_id
            // Filter grade yang mata pelajarannya diampu oleh guru ini
            $q->whereIn('subject_id', function($query) use ($teacher) {
                $query->select('subject_id')->from('class_subject_teacher')->where('teacher_id', $teacher->id);
            });
        })->withCount('students')
            ->get()
            ->groupBy('major');

        // Jadwal Mengajar Hari Ini
        $activeYear = AcademicYear::where('is_active', true)->first();
        $todaySchedules = collect();
        $todayIndo = '';

        if ($activeYear) {
            $daysIndo = [
                0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
                3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
            ];
            $todayIndo = $daysIndo[now()->dayOfWeek];

            if ($todayIndo !== 'Minggu') {
                $rawSchedules = Schedule::where('teacher_id', $teacher->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('day', $todayIndo)
                    ->with(['schoolClass', 'timeSlot', 'subject'])
                    ->get();

                $todaySchedules = Schedule::groupConsecutiveSchedules($rawSchedules);

                // Attach existing meeting for today if already created
                foreach ($todaySchedules as $block) {
                    if ($block->subject_id && $block->class_id) {
                        $block->existingMeeting = \App\Models\Meeting::where('teacher_id', $teacher->id)
                            ->where('class_id', $block->class_id)
                            ->where('subject_id', $block->subject_id)
                            ->where('date', now()->toDateString())
                            ->first();
                    } else {
                        $block->existingMeeting = null;
                    }
                }
            }
        }

        return view('guru.dashboard', compact(
            'meetingsCount',
            'materialsCount',
            'assignmentsCount',
            'pendingGradesCount',
            'recentPendingAssignments',
            'assignedClasses',
            'todaySchedules',
            'todayIndo'
        ));
    }

    public function index(Request $request): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        $query = Material::where('teacher_id', $teacherId)
            ->with(['schoolClass', 'subject', 'meeting']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $materials = $query->latest()->paginate(10)->appends($request->query());

        // Get teacher's classes for filter dropdown
        $teacherClasses = SchoolClass::whereHas('materials', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->orderBy('name')->get();

        $selectedClassId = $request->class_id;

        return view('guru.materials.index', compact('materials', 'teacherClasses', 'selectedClassId'));
    }

    public function create(): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->first() ?? Teacher::first();
        $classes = SchoolClass::orderBy('name')->get();
        
        $assignedSubjectIds = $teacher ? $teacher->teachingAssignments()->pluck('subject_id') : collect();
        $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('name')->get();
        }

        $meetings = $teacher ? Meeting::where('teacher_id', $teacher->id)->orderBy('number')->get() : Meeting::orderBy('number')->get();

        return view('guru.materials.create', compact('classes', 'subjects', 'meetings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        if (!$teacherId && Auth::user()->role === 'admin') {
            $teacherId = $request->meeting_id ? Meeting::where('id', $request->meeting_id)->value('teacher_id') : Teacher::value('id');
        }
        abort_unless($teacherId, 403);

        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'meeting_id' => ['nullable', 'exists:meetings,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // Max 10MB
            'youtube_url' => ['nullable', 'url', 'max:255'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('materials', 'public');
        }

        $material = Material::create([
            'teacher_id' => $teacherId,
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'meeting_id' => $data['meeting_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'file_path' => $filePath,
            'youtube_url' => $data['youtube_url'] ?? null,
        ]);

        // Create notifications for all students in class
        try {
            $students = \App\Models\Student::where('class_id', $material->class_id)->get();
            $subjectName = $material->subject ? $material->subject->name : 'Mata Pelajaran';
            foreach ($students as $student) {
                \App\Models\Notification::create([
                    'user_id' => $student->user_id,
                    'title' => '📚 Materi Baru: ' . $material->title,
                    'message' => 'Guru telah menambahkan materi baru untuk mata pelajaran ' . $subjectName . '.',
                    'url' => route('siswa.materials.show', $material->id),
                ]);
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        if ($material->meeting_id) {
            $route = Auth::user()->role === 'admin' ? 'admin.attendances.meetingMaterials' : 'guru.meetings.show';
            return redirect()->route($route, $material->meeting_id)
                ->with('success', 'Materi berhasil diupload.');
        }

        return redirect()->route('guru.materials.index')
            ->with('success', 'Materi berhasil diupload.');
    }

    public function show(Material $material): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $material->teacher_id == $teacherId, 403);
        return view('guru.materials.show', compact('material'));
    }

    public function edit(Material $material): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $material->teacher_id == $teacherId, 403);

        $teacher = Teacher::where('user_id', Auth::id())->first() ?? Teacher::find($material->teacher_id);
        $classes = SchoolClass::orderBy('name')->get();
        
        $assignedSubjectIds = $teacher ? $teacher->teachingAssignments()->pluck('subject_id') : collect();
        $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('name')->get();
        }

        $meetings = Meeting::where('class_id', $material->class_id)->where('subject_id', $material->subject_id)->orderBy('number')->get();

        return view('guru.materials.edit', compact('material', 'classes', 'subjects', 'meetings'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $material->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'meeting_id' => ['nullable', 'exists:meetings,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
        ]);

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $data['file_path'] = $request->file('file')->store('materials', 'public');
        }

        $material->update($data);

        if ($material->meeting_id) {
            $route = Auth::user()->role === 'admin' ? 'admin.attendances.meetingMaterials' : 'guru.meetings.show';
            return redirect()->route($route, $material->meeting_id)
                ->with('success', 'Materi berhasil diperbarui.');
        }

        return redirect()->route('guru.materials.index')
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $material->teacher_id == $teacherId, 403);

        $meetingId = $material->meeting_id;
        $material->delete();

        if (Auth::user()->role === 'admin' && $meetingId) {
            return redirect()->route('admin.attendances.meetingMaterials', $meetingId)
                ->with('success', 'Materi berhasil dihapus.');
        }

        return redirect()->route('guru.materials.index')
            ->with('success', 'Materi berhasil dihapus.');
    }
}
