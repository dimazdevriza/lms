<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Meeting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher.user'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.classes.index', compact('classes'));
    }

    public function show(SchoolClass $class): View
    {
        $class->load(['academicYear', 'homeroomTeacher.user', 'students.user']);
        $class->loadCount('students');

        // Retrieve subjects linked to this class via schedules, meetings, or class_subject_teacher
        $subjectIdsFromSchedules = DB::table('schedules')->where('class_id', $class->id)->pluck('subject_id');
        $subjectIdsFromMeetings = DB::table('meetings')->where('class_id', $class->id)->pluck('subject_id');
        $subjectIdsFromAssignments = DB::table('class_subject_teacher')->where('class_id', $class->id)->pluck('subject_id');

        $allSubjectIds = $subjectIdsFromSchedules
            ->concat($subjectIdsFromMeetings)
            ->concat($subjectIdsFromAssignments)
            ->unique()
            ->filter();

        $subjects = Subject::whereIn('id', $allSubjectIds)
            ->orderBy('name')
            ->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('name')->get();
        }

        foreach ($subjects as $subject) {
            $subject->meeting_count = Meeting::where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->count();

            $subject->completed_attendance_count = Meeting::where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->whereHas('attendance')
                ->count();
        }

        $students = $class->students->sortBy(fn($s) => strtolower($s->user?->name ?? ''), SORT_NATURAL)->values();

        return view('admin.classes.show', compact('class', 'subjects', 'students'));
    }

    public function create(): View
    {
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get();
        $teachers = Teacher::with('user')->orderByDesc('id')->get();

        return view('admin.classes.create', compact('years', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:10'],
            'major' => ['nullable', 'string', 'max:50'], // IPA, IPS
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        SchoolClass::create($data);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function edit(SchoolClass $class): View
    {
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get();
        $teachers = Teacher::with('user')->orderByDesc('id')->get();

        return view('admin.classes.edit', compact('class', 'years', 'teachers'));
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:10'],
            'major' => ['nullable', 'string', 'max:50'], // IPA, IPS
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $class->update($data);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}

