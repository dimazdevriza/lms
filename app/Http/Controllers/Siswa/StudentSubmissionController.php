<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionAnswer;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentSubmissionController extends Controller
{
    public function index(): View
    {
        $student = Student::where('user_id', Auth::id())->first();

        $assignments = Assignment::query()
            ->when($student?->class_id, fn ($q, $classId) => $q->where('class_id', $classId))
            ->where(function ($query) {
                $query->whereNull('meeting_id')
                    ->orWhereHas('meeting', function ($q) {
                        $q->where('is_visible', true);
                    });
            })
            ->latest('due_at')
            ->get();

        // Load existing submissions for this student
        if ($student) {
            $submittedIds = AssignmentSubmission::where('student_id', $student->id)
                ->pluck('assignment_id')
                ->toArray();
        } else {
            $submittedIds = [];
        }

        return view('siswa.assignments.index', compact('assignments', 'student', 'submittedIds'));
    }

    /**
     * Show an online assignment for the student to answer
     */
    public function show(Assignment $assignment): View
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();
        
        // Verify student belongs to this class
        abort_unless($assignment->class_id == $student->class_id, 403);

        // Verify meeting visibility if associated with one
        if ($assignment->meeting_id) {
            abort_unless($assignment->meeting?->is_visible, 404);
        }

        $assignment->load('questions.options');

        // Check if already submitted
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        $answers = [];
        if ($submission) {
            $submission->load(['questionAnswers.selectedOption', 'comments.user']);
            // Index answers by question_id for easy lookup
            $answers = $submission->questionAnswers->keyBy('question_id');
        }

        return view('siswa.assignments.show', compact('assignment', 'student', 'submission', 'answers'));
    }

    /**
     * Submit or update answers for a PDF or online assignment
     */
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        // Verify student belongs to this class
        abort_unless($assignment->class_id == $student->class_id, 403);

        // Verify meeting visibility if associated with one
        if ($assignment->meeting_id) {
            abort_unless($assignment->meeting?->is_visible, 404);
        }

        // Enforce deadline — tolak submission jika deadline sudah lewat
        if ($assignment->due_at && Carbon::parse($assignment->due_at)->isPast()) {
            return back()->withErrors(['general' => 'Batas waktu pengumpulan tugas ini sudah lewat. Anda tidak dapat mengumpulkan atau mengubah tugas lagi.']);
        }

        // Check if existing submission has already been graded
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission && $existingSubmission->score !== null) {
            return back()->withErrors(['general' => 'Tugas sudah dinilai oleh guru dan tidak dapat diubah lagi.']);
        }

        if ($assignment->isOnline()) {
            return $this->storeOnline($request, $assignment, $student);
        }

        return $this->storePdf($request, $assignment, $student);
    }

    /**
     * Handle PDF assignment submission or edit
     */
    private function storePdf(Request $request, Assignment $assignment, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'answer_text' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('submissions', 'local');
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($submission) {
            if ($filePath && $submission->file_path && $submission->file_path !== $filePath) {
                Storage::disk('local')->delete($submission->file_path);
            }
            $submission->update([
                'answer_text' => array_key_exists('answer_text', $data) ? $data['answer_text'] : $submission->answer_text,
                'file_path' => $filePath ?? $submission->file_path,
                'submitted_at' => now(),
            ]);
            $msg = 'Tugas berhasil diperbarui.';
        } else {
            try {
                AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'answer_text' => $data['answer_text'] ?? null,
                    'file_path' => $filePath,
                    'submitted_at' => now(),
                ]);
                $msg = 'Tugas berhasil dikirim.';
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->first();
                if ($submission) {
                    if ($filePath && $submission->file_path && $submission->file_path !== $filePath) {
                        Storage::disk('local')->delete($submission->file_path);
                    }
                    $submission->update([
                        'answer_text' => array_key_exists('answer_text', $data) ? $data['answer_text'] : $submission->answer_text,
                        'file_path' => $filePath ?? $submission->file_path,
                        'submitted_at' => now(),
                    ]);
                    $msg = 'Tugas berhasil diperbarui.';
                }
            }
        }

        // Notify Teacher of submission
        try {
            $teacherUser = $assignment->teacher?->user;
            if ($teacherUser) {
                \App\Models\Notification::create([
                    'user_id' => $teacherUser->id,
                    'title' => '📥 Tugas Dikumpulkan/Diperbarui: ' . Auth::user()->name,
                    'message' => Auth::user()->name . ' telah mengumpulkan/memperbarui tugas ' . $assignment->title . '.',
                    'url' => route('guru.assignments.show', $assignment->id),
                ]);
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        return back()->with('success', $msg);
    }

    /**
     * Handle online assignment submission or edit
     */
    private function storeOnline(Request $request, Assignment $assignment, Student $student): RedirectResponse
    {
        $assignment->load('questions.options');
        $questions = $assignment->questions;

        // Check if existing submission
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        // Validate that ALL questions are answered
        $answersData = $request->input('answers', []);
        $unanswered = [];

        foreach ($questions as $question) {
            $answer = $answersData[$question->id] ?? null;

            if ($question->type === 'pilihan_ganda') {
                if (empty($answer['selected_option_id'])) {
                    $unanswered[] = $question->order;
                }
            } elseif ($question->type === 'isian_singkat') {
                if (empty(trim($answer['answer_text'] ?? ''))) {
                    $unanswered[] = $question->order;
                }
            } elseif ($question->type === 'essay') {
                if (empty(trim($answer['answer_text'] ?? ''))) {
                    $unanswered[] = $question->order;
                }
            }
        }

        if (!empty($unanswered)) {
            $nums = implode(', ', $unanswered);
            return back()->withInput()->withErrors([
                'general' => "Semua soal wajib dijawab. Soal nomor {$nums} belum dijawab."
            ]);
        }

        DB::beginTransaction();
        try {
            if (!$existingSubmission) {
                $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->first();
            }

            if ($existingSubmission) {
                QuestionAnswer::where('assignment_submission_id', $existingSubmission->id)->delete();
                $submission = $existingSubmission;
                $submission->update(['submitted_at' => now()]);
                $msg = 'Jawaban berhasil diperbarui!';
            } else {
                try {
                    $submission = AssignmentSubmission::create([
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                        'submitted_at' => now(),
                    ]);
                    $msg = 'Jawaban berhasil dikirim!';
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                        ->where('student_id', $student->id)
                        ->first();
                    QuestionAnswer::where('assignment_submission_id', $submission->id)->delete();
                    $submission->update(['submitted_at' => now()]);
                    $msg = 'Jawaban berhasil diperbarui!';
                }
            }

            $totalScore = 0;
            $totalPoints = 0;

            foreach ($questions as $question) {
                $answer = $answersData[$question->id] ?? [];
                $isCorrect = null;
                $score = null;
                $selectedOptionId = null;
                $answerText = null;

                $totalPoints += $question->points;

                if ($question->type === 'pilihan_ganda') {
                    $selectedOptionId = $answer['selected_option_id'] ?? null;
                    if ($selectedOptionId) {
                        $correctOption = $question->options->where('is_correct', true)->first();
                        $isCorrect = $correctOption && $correctOption->id == $selectedOptionId;
                        $score = $isCorrect ? $question->points : 0;
                        $totalScore += $score;
                    }
                } elseif ($question->type === 'isian_singkat') {
                    $answerText = trim($answer['answer_text'] ?? '');
                    if ($question->correct_answer) {
                        $isCorrect = mb_strtolower(trim($answerText)) === mb_strtolower(trim($question->correct_answer));
                        $score = $isCorrect ? $question->points : 0;
                        $totalScore += $score;
                    }
                } elseif ($question->type === 'essay') {
                    $answerText = trim($answer['answer_text'] ?? '');
                    $isCorrect = null;
                    $score = null;
                }

                QuestionAnswer::create([
                    'question_id' => $question->id,
                    'student_id' => $student->id,
                    'assignment_submission_id' => $submission->id,
                    'answer_text' => $answerText,
                    'selected_option_id' => $selectedOptionId,
                    'is_correct' => $isCorrect,
                    'score' => $score,
                ]);
            }

            // Calculate percentage score (only from auto-graded questions)
            $hasEssay = $questions->where('type', 'essay')->count() > 0;
            if (!$hasEssay && $totalPoints > 0) {
                $percentage = round(($totalScore / $totalPoints) * 100);
                $submission->update(['score' => $percentage]);
            } else {
                $submission->update(['score' => null]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'Terjadi kesalahan saat menyimpan jawaban: ' . $e->getMessage()]);
        }

        // Notify Teacher of submission
        try {
            $teacherUser = $assignment->teacher?->user;
            if ($teacherUser) {
                \App\Models\Notification::create([
                    'user_id' => $teacherUser->id,
                    'title' => '📥 Tugas Dikumpulkan/Diperbarui: ' . Auth::user()->name,
                    'message' => Auth::user()->name . ' telah mengumpulkan/memperbarui tugas ' . $assignment->title . '.',
                    'url' => route('guru.assignments.show', $assignment->id),
                ]);
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        return redirect()->route('siswa.assignments.show', $assignment)
            ->with('success', $msg);
    }

    public function unsubmit(Assignment $assignment): RedirectResponse
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        // Verify student belongs to this class
        abort_unless($assignment->class_id == $student->class_id, 403);

        // Verify meeting visibility if associated with one
        if ($assignment->meeting_id) {
            abort_unless($assignment->meeting?->is_visible, 404);
        }
        
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Enforce deadline — tolak pembatalan jika deadline sudah lewat
        if ($assignment->due_at && Carbon::parse($assignment->due_at)->isPast()) {
            return back()->withErrors(['general' => 'Batas waktu pengumpulan tugas ini sudah lewat. Pengiriman tugas tidak dapat dibatalkan.']);
        }

        if ($submission->score !== null) {
            return back()->withErrors(['general' => 'Tugas sudah dinilai oleh guru dan tidak dapat dibatalkan.']);
        }

        if ($submission->file_path) {
            Storage::disk('local')->delete($submission->file_path);
        }

        // Delete question answers if online assignment
        QuestionAnswer::where('assignment_submission_id', $submission->id)->delete();

        $submission->delete();

        return back()->with('success', 'Pengiriman tugas berhasil dibatalkan.');
    }
}
