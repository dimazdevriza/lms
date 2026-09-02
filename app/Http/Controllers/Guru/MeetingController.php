<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ClassSubjectTeacher;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        // Tampilan awal: Grup Kelas & Mapel yang diajar
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        $assignments = collect([]);
        if ($activeYear) {
            $assignments = ClassSubjectTeacher::where('teacher_id', $teacherId)
                ->where('academic_year_id', $activeYear->id)
                ->with(['schoolClass', 'subject'])
                ->get();
        }

        // Hitung total meeting yang sudah dibuat untuk tiap kelommpok
        $meetingCounts = Meeting::where('teacher_id', $teacherId)
            ->select('class_id', 'subject_id')
            ->selectRaw('count(*) as total_meetings')
            ->groupBy('class_id', 'subject_id')
            ->get()
            ->keyBy(function($item) {
                return $item->class_id . '_' . $item->subject_id;
            });

        $meetingGroups = $assignments->map(function($assignment) use ($meetingCounts) {
            $key = $assignment->class_id . '_' . $assignment->subject_id;
            $assignment->total_meetings = $meetingCounts->has($key) ? $meetingCounts->get($key)->total_meetings : 0;
            return $assignment;
        })
        ->groupBy(function ($group) {
            return $group->schoolClass->major ?? 'Umum';
        })
        ->map(function ($majorGroup) {
            return $majorGroup->groupBy(function ($item) {
                return $item->subject->name ?? 'Tanpa Mata Pelajaran';
            });
        });

        return view('guru.meetings.index', compact('meetingGroups'));
    }

    public function create(Request $request): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        if ($activeYear) {
            $assignedClassIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('class_id');
            $classes = SchoolClass::whereIn('id', $assignedClassIds)->orderBy('name')->get();
            
            $assignedSubjectIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('subject_id');
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        } else {
            $classes = SchoolClass::orderBy('name')->get();
            $assignedSubjectIds = $teacher->teachingAssignments()->pluck('subject_id');
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
            if ($subjects->isEmpty()) {
                $subjects = Subject::orderBy('name')->get();
            }
        }

        // Auto-fill logic untuk pertemuan lanjutan
        $prefilledClassId = $request->query('class_id');
        $prefilledSubjectId = $request->query('subject_id');
        $nextNumber = 1;

        if ($prefilledClassId && $prefilledSubjectId) {
            $lastMeeting = Meeting::where('teacher_id', $teacher->id)
                ->where('class_id', $prefilledClassId)
                ->where('subject_id', $prefilledSubjectId)
                ->orderBy('number', 'desc')
                ->first();
            
            if ($lastMeeting) {
                $nextNumber = $lastMeeting->number + 1;
            }
        }

        // ponytail: fetch class and subject models to support direct back route
        $currentClass = $prefilledClassId ? SchoolClass::find($prefilledClassId) : null;
        $currentSubject = $prefilledSubjectId ? Subject::find($prefilledSubjectId) : null;

        return view('guru.meetings.create', compact('classes', 'subjects', 'prefilledClassId', 'prefilledSubjectId', 'nextNumber', 'currentClass', 'currentSubject'));
    }

    public function classMeetingsIndex($classSlug, $subjectSlug): View
    {
        $currentClass = SchoolClass::where('slug', $classSlug)->firstOrFail();
        $currentSubject = Subject::where('slug', $subjectSlug)->firstOrFail();
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        
        $query = Meeting::where('class_id', $currentClass->id)
            ->where('subject_id', $currentSubject->id)
            ->with(['schoolClass', 'subject'])
            ->orderBy('number', 'desc');

        if (Auth::user()->role !== 'admin' && $teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        $meetings = $query->get();

        return view('guru.meetings.list', compact('meetings', 'currentClass', 'currentSubject'));
    }

    public function classMeetingsCreate($classSlug, $subjectSlug): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->first() ?? Teacher::first();
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        $currentClass = SchoolClass::where('slug', $classSlug)->firstOrFail();
        $currentSubject = Subject::where('slug', $subjectSlug)->firstOrFail();

        if ($activeYear && $teacher) {
            $assignedClassIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('class_id');
            $classes = SchoolClass::whereIn('id', $assignedClassIds)->orderBy('name')->get();
            
            $assignedSubjectIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('subject_id');
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        } else {
            $classes = SchoolClass::orderBy('name')->get();
            $assignedSubjectIds = $teacher ? $teacher->teachingAssignments()->pluck('subject_id') : collect();
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
            if ($subjects->isEmpty()) {
                $subjects = Subject::orderBy('name')->get();
            }
        }

        $lastMeeting = Meeting::where('class_id', $currentClass->id)
            ->where('subject_id', $currentSubject->id)
            ->orderBy('number', 'desc')
            ->first();
        
        $nextNumber = $lastMeeting ? $lastMeeting->number + 1 : 1;

        $prefilledClassId = $currentClass->id;
        $prefilledSubjectId = $currentSubject->id;

        // ponytail: pass currentClass and currentSubject to enable correct back route
        return view('guru.meetings.create', compact('classes', 'subjects', 'prefilledClassId', 'prefilledSubjectId', 'nextNumber', 'currentClass', 'currentSubject'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_link' => ['nullable', 'url', 'max:255'],
            'date' => ['required', 'date'],
            'number' => ['required', 'integer', 'min:1'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        $data['is_visible'] = $request->boolean('is_visible');
        Meeting::create(array_merge($data, ['teacher_id' => $teacherId]));

        $class = SchoolClass::findOrFail($data['class_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        return redirect()->route('guru.meetings.class-meetings', [
            'classSlug' => $class->slug,
            'subjectSlug' => $subject->slug,
        ])->with('success', 'Pertemuan berhasil dibuat.');
    }

    public function edit(Meeting $meeting): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $meeting->teacher_id == $teacherId, 403);

        $teacher = Teacher::where('user_id', Auth::id())->first() ?? Teacher::find($meeting->teacher_id);
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        if ($activeYear && $teacher) {
            $assignedClassIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('class_id');
            $classes = SchoolClass::whereIn('id', $assignedClassIds)->orderBy('name')->get();
            
            $assignedSubjectIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('subject_id');
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        } else {
            $classes = SchoolClass::orderBy('name')->get();
            $assignedSubjectIds = $teacher ? $teacher->teachingAssignments()->pluck('subject_id') : collect();
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
            if ($subjects->isEmpty()) {
                $subjects = Subject::orderBy('name')->get();
            }
        }

        return view('guru.meetings.edit', compact('meeting', 'classes', 'subjects'));
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $meeting->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_link' => ['nullable', 'url', 'max:255'],
            'date' => ['required', 'date'],
            'number' => ['required', 'integer', 'min:1'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $data['is_visible'] = $request->boolean('is_visible');
        $meeting->update($data);

        // ponytail: redirect to specific class meetings index instead of general dashboard
        $class = SchoolClass::findOrFail($data['class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        return redirect()->route('guru.meetings.class-meetings', [
            'classSlug' => $class->slug,
            'subjectSlug' => $subject->slug,
        ])->with('success', 'Pertemuan berhasil diperbarui.');
    }

    public function destroy(Meeting $meeting): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(($meeting->teacher_id == $teacherId && session()->has('impersonate_original_id')) || Auth::user()->role === 'admin', 403, 'Aksi ini hanya dapat dilakukan dalam mode Impersonasi Admin.');

        $classId = $meeting->class_id;
        $subjectId = $meeting->subject_id;

        $meeting->delete();

        // ponytail: redirect to specific class meetings index using slugs
        $class = SchoolClass::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);
        return redirect()->route('guru.meetings.class-meetings', [
            'classSlug' => $class->slug,
            'subjectSlug' => $subject->slug,
        ])->with('success', 'Pertemuan berhasil dihapus.');
    }

    public function show(Meeting $meeting): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $meeting->teacher_id == $teacherId, 403);

        $meeting->load(['materials', 'assignments', 'attendance.details.student.user']);

        $discussionPosts = \App\Models\ForumPost::where('meeting_id', $meeting->id)
            ->with([
                'user',
                'comments' => function($q) { $q->whereNull('parent_id')->with('user'); },
                'comments.replies.user',
                'comments.replies.replies.user',
                'comments.replies.replies.replies.user'
            ])
            ->latest()
            ->get();

        return view('guru.meetings.show', compact('meeting', 'discussionPosts'));
    }

    public function updateVideoLink(Request $request, Meeting $meeting): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $meeting->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'video_link' => ['nullable', 'url', 'max:255'],
            'video_link_status' => ['nullable', 'string', 'in:active,finished'],
        ]);

        if (empty($data['video_link'])) {
            $data['video_link_status'] = 'active';
        }

        $meeting->update($data);

        return back()->with('success', 'Link video conference berhasil diperbarui.');
    }

    public function toggleVisibility(Meeting $meeting): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $meeting->teacher_id == $teacherId, 403);

        $meeting->update([
            'is_visible' => !$meeting->is_visible
        ]);

        return back()->with('success', $meeting->is_visible ? 'Pertemuan sekarang terlihat oleh siswa.' : 'Pertemuan berhasil disembunyikan dari siswa.');
    }
}
