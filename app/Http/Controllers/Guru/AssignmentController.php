<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Meeting;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ClassSubjectTeacher;
use App\Models\AssignmentSubmission;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        $query = Assignment::where('teacher_id', $teacherId)
            ->with(['schoolClass', 'subject', 'meeting', 'submissions']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $assignments = $query->latest()->paginate(10)->appends($request->query());

        // Get teacher's classes for filter
        $teacherClasses = SchoolClass::whereHas('assignments', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->orderBy('name')->get();

        $selectedClassId = $request->class_id;

        return view('guru.assignments.index', compact('assignments', 'teacherClasses', 'selectedClassId'));
    }

    public function grading(Request $request): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        $query = Assignment::where('teacher_id', $teacherId)
            ->with(['submissions.student.user', 'schoolClass', 'subject'])
            ->withCount('submissions');

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by grading status
        $filter = $request->get('filter');
        if ($filter === 'pending') {
            $query->whereHas('submissions', function ($q) {
                $q->whereNull('score');
            });
        } elseif ($filter === 'graded') {
            $query->whereHas('submissions', function ($q) {
                $q->whereNotNull('score');
            })->whereDoesntHave('submissions', function ($q) {
                $q->whereNull('score');
            });
        }

        $assignments = $query->latest()->paginate(10)->appends($request->query());

        // Summary stats (filtered by class too if selected)
        $statsQuery = Assignment::where('teacher_id', $teacherId);
        if ($request->filled('class_id')) {
            $statsQuery->where('class_id', $request->class_id);
        }
        $allAssignments = $statsQuery->with('submissions')->get();
        $totalAssignments = $allAssignments->count();
        $totalSubmissions = $allAssignments->sum(fn($a) => $a->submissions->count());
        $pendingGrading = $allAssignments->sum(fn($a) => $a->submissions->whereNull('score')->count());
        $gradedSubmissions = $totalSubmissions - $pendingGrading;

        // Get teacher's classes for filter
        $teacherClasses = SchoolClass::whereHas('assignments', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->orderBy('name')->get();

        $selectedClassId = $request->class_id;

        return view('guru.assignments.grading', compact(
            'assignments', 'totalAssignments', 'totalSubmissions', 'pendingGrading', 'gradedSubmissions',
            'teacherClasses', 'selectedClassId'
        ));
    }

    public function create(): View
    {
        $teacher = Teacher::where('user_id', Auth::id())->first() ?? Teacher::first();
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

        $meetings = $teacher ? Meeting::where('teacher_id', $teacher->id)->orderBy('number')->get() : Meeting::orderBy('number')->get();

        return view('guru.assignments.create', compact('classes', 'subjects', 'meetings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'meeting_id' => ['nullable', 'exists:meetings,id'],
            'type' => ['required', 'in:pdf,online,external'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'],
            // Online questions come as JSON
            'questions_json' => ['nullable', 'string'],
            'quiz_url' => ['nullable', 'url', 'max:255'],
        ]);

        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        if (!$teacherId && Auth::user()->role === 'admin') {
            $teacherId = Meeting::where('id', $request->meeting_id)->value('teacher_id') ?? Teacher::value('id');
        }
        abort_unless($teacherId, 403);

        $filePath = null;
        if ($data['type'] === 'pdf' && $request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignments', 'local');
        }

        DB::beginTransaction();
        try {
            $assignment = Assignment::create([
                'teacher_id' => $teacherId,
                'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'meeting_id' => $data['meeting_id'] ?? null,
                'type' => $data['type'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_at' => $data['due_at'] ?? null,
                'file_path' => $filePath,
                'quiz_url' => $data['type'] === 'external' ? ($request->quiz_url) : null,
            ]);

            // Create questions for online assignments
            if ($data['type'] === 'online' && !empty($data['questions_json'])) {
                $questions = json_decode($data['questions_json'], true);
                
                if (!is_array($questions) || count($questions) === 0) {
                    throw new \Exception('Minimal 1 soal harus ditambahkan untuk tugas online.');
                }

                foreach ($questions as $index => $q) {
                    $question = Question::create([
                        'assignment_id' => $assignment->id,
                        'type' => $q['type'],
                        'body' => $q['body'],
                        'image' => $q['image'] ?? null,
                        'order' => $index + 1,
                        'points' => $q['points'] ?? 1,
                        'correct_answer' => $q['type'] === 'isian_singkat' ? ($q['correct_answer'] ?? null) : null,
                    ]);

                    // Create options for pilihan_ganda
                    if ($q['type'] === 'pilihan_ganda' && !empty($q['options'])) {
                        foreach ($q['options'] as $opt) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'label' => $opt['label'],
                                'body' => $opt['body'],
                                'image' => $opt['image'] ?? null,
                                'is_correct' => $opt['is_correct'] ?? false,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Create notifications for all students in class
            try {
                $students = \App\Models\Student::where('class_id', $assignment->class_id)->get();
                $subjectName = $assignment->subject ? $assignment->subject->name : 'Mata Pelajaran';
                foreach ($students as $student) {
                    \App\Models\Notification::create([
                        'user_id' => $student->user_id,
                        'title' => '📝 Tugas Baru: ' . $assignment->title,
                        'message' => 'Guru telah menambahkan tugas baru untuk mata pelajaran ' . $subjectName . '.',
                        'url' => route('siswa.assignments.show', $assignment->id),
                    ]);
                }
            } catch (\Exception $ne) {
                // Silently ignore notification creation failures
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['questions_json' => $e->getMessage()]);
        }

        if ($assignment->meeting_id) {
            $route = Auth::user()->role === 'admin' ? 'admin.attendances.meetingAssignments' : 'guru.meetings.show';
            return redirect()->route($route, $assignment->meeting_id)
                ->with('success', 'Tugas berhasil dibuat.');
        }

        return redirect()->route('guru.assignments.index')
            ->with('success', 'Tugas berhasil dibuat.');
    }

    public function edit(Assignment $assignment): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $assignment->teacher_id == $teacherId, 403);

        $teacher = Teacher::where('user_id', Auth::id())->first() ?? Teacher::find($assignment->teacher_id);
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

        $meetings = Meeting::where('class_id', $assignment->class_id)->where('subject_id', $assignment->subject_id)->orderBy('number')->get();

        // Load questions with options for online assignments
        if ($assignment->isOnline()) {
            $assignment->load('questions.options');
        }

        $hasSubmissions = $assignment->submissions()->count() > 0;

        return view('guru.assignments.edit', compact('assignment', 'classes', 'subjects', 'meetings', 'hasSubmissions'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $assignment->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'meeting_id' => ['nullable', 'exists:meetings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'],
            'questions_json' => ['nullable', 'string'],
            'quiz_url' => ['nullable', 'url', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            if ($assignment->type === 'pdf' && $request->hasFile('file')) {
                if ($assignment->file_path) {
                    Storage::disk('local')->delete($assignment->file_path);
                }
                $data['file_path'] = $request->file('file')->store('assignments', 'local');
            }

            if ($assignment->type === 'external') {
                $data['quiz_url'] = $request->quiz_url;
            } else {
                $data['quiz_url'] = null;
            }

            $assignment->update($data);

            // Update questions for online assignments (only if no submissions yet)
            if ($assignment->isOnline() && !empty($data['questions_json']) && $assignment->submissions()->count() === 0) {
                // Delete existing questions and recreate
                $assignment->questions()->delete();
                
                $questions = json_decode($data['questions_json'], true);
                
                if (!is_array($questions) || count($questions) === 0) {
                    throw new \Exception('Minimal 1 soal harus ditambahkan untuk tugas online.');
                }

                foreach ($questions as $index => $q) {
                    $question = Question::create([
                        'assignment_id' => $assignment->id,
                        'type' => $q['type'],
                        'body' => $q['body'],
                        'image' => $q['image'] ?? null,
                        'order' => $index + 1,
                        'points' => $q['points'] ?? 1,
                        'correct_answer' => $q['type'] === 'isian_singkat' ? ($q['correct_answer'] ?? null) : null,
                    ]);

                    if ($q['type'] === 'pilihan_ganda' && !empty($q['options'])) {
                        foreach ($q['options'] as $opt) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'label' => $opt['label'],
                                'body' => $opt['body'],
                                'image' => $opt['image'] ?? null,
                                'is_correct' => $opt['is_correct'] ?? false,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['questions_json' => $e->getMessage()]);
        }

        if ($assignment->meeting_id) {
            $route = Auth::user()->role === 'admin' ? 'admin.attendances.meetingAssignments' : 'guru.meetings.show';
            return redirect()->route($route, $assignment->meeting_id)
                ->with('success', 'Tugas berhasil diperbarui.');
        }

        return redirect()->route('guru.assignments.index')
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function show(Assignment $assignment): View
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $assignment->teacher_id == $teacherId, 403);

        $assignment->load(['submissions.student.user', 'submissions.comments.user', 'schoolClass']);

        if ($assignment->isOnline()) {
            $assignment->load(['questions.options', 'submissions.questionAnswers.question', 'submissions.questionAnswers.selectedOption']);
        }

        $classId = $assignment->class_id ?? $assignment->meeting?->class_id;

        $allStudents = $classId 
            ? Student::where('class_id', $classId)->with('user')->get() 
            : collect();

        $submittedStudentIds = $assignment->submissions->pluck('student_id')->toArray();
        $submittedSubmissions = $assignment->submissions->sortBy(fn($sub) => strtolower($sub->student?->user?->name ?? ''));
        $unsubmittedStudents = $allStudents->reject(fn($s) => in_array($s->id, $submittedStudentIds))
            ->sortBy(fn($s) => strtolower($s->user?->name ?? ''));

        return view('guru.assignments.show', compact('assignment', 'allStudents', 'submittedSubmissions', 'unsubmittedStudents'));
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $assignment->teacher_id == $teacherId, 403);

        $meetingId = $assignment->meeting_id;
        $assignment->delete();

        // ponytail: redirect back to meeting details if assignment belonged to one
        if ($meetingId) {
            $route = Auth::user()->role === 'admin' ? 'admin.attendances.meetingAssignments' : 'guru.meetings.show';
            return redirect()->route($route, $meetingId)
                ->with('success', 'Tugas berhasil dihapus.');
        }

        return redirect()->route('guru.assignments.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Grade a single question answer (for essay questions)
     */
    public function gradeQuestion(Request $request, QuestionAnswer $answer)
    {
        // Verify the teacher owns this assignment
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        $assignment = $answer->question->assignment;
        abort_unless($assignment->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0'],
            'is_correct' => ['required', 'boolean'],
        ]);

        // Ensure score doesn't exceed max points for this question
        $maxPoints = $answer->question->points;
        if ($data['score'] > $maxPoints) {
            $data['score'] = $maxPoints;
        }

        $answer->update([
            'score' => $data['score'],
            'is_correct' => $data['is_correct'],
        ]);

        // Recalculate total submission score
        $submission = $answer->submission;
        $totalScore = $submission->questionAnswers()->sum('score');
        $totalPoints = $assignment->questions()->sum('points');
        
        // Calculate as percentage (0-100)
        $percentage = $totalPoints > 0 ? round(($totalScore / $totalPoints) * 100) : 0;
        $submission->update(['score' => $percentage]);

        // Create notification for student
        try {
            $studentUser = $submission->student->user;
            \App\Models\Notification::create([
                'user_id' => $studentUser->id,
                'title' => '⭐ Nilai Tugas Diperbarui: ' . $assignment->title,
                'message' => 'Nilai Anda untuk tugas ' . $assignment->title . ' telah diperbarui menjadi: ' . $percentage . '.',
                'url' => route('siswa.assignments.show', $assignment->id),
            ]);
        } catch (\Exception $ne) {
            // Silently ignore
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Nilai soal berhasil disimpan.',
                'score' => $data['score'],
                'is_correct' => $data['is_correct'],
                'total_score' => $percentage,
            ]);
        }

        return back()->with('success', 'Nilai soal berhasil disimpan.');
    }

    /**
     * Grade a full assignment submission (for PDF assignments)
     */
    public function gradeSubmission(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        // Verify the teacher owns this assignment
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($submission->assignment->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'],
        ]);

        // Create notification for student
        try {
            $studentUser = $submission->student->user;
            \App\Models\Notification::create([
                'user_id' => $studentUser->id,
                'title' => '⭐ Nilai Tugas Dirilis: ' . $submission->assignment->title,
                'message' => 'Nilai Anda untuk tugas ' . $submission->assignment->title . ' telah dirilis dengan nilai: ' . $data['score'] . '.',
                'url' => route('siswa.assignments.show', $submission->assignment_id),
            ]);
        } catch (\Exception $ne) {
            // Silently ignore
        }

        return back()->with('success', 'Nilai tugas berhasil disimpan.');
    }

    public function extendDeadline(Request $request, Assignment $assignment): RedirectResponse
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless(Auth::user()->role === 'admin' || $assignment->teacher_id == $teacherId, 403);

        $data = $request->validate([
            'due_at' => ['required', 'date'],
        ]);

        $assignment->update([
            'due_at' => $data['due_at'],
        ]);

        return back()->with('success', 'Batas waktu pengumpulan tugas berhasil diperpanjang.');
    }
}
