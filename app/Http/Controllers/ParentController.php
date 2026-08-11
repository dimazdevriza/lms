<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassAttendanceDetail;
use App\Models\AttendanceDetail;
use App\Models\StudentGrade;
use App\Models\AssignmentSubmission;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParentController extends Controller
{
    /**
     * Durasi timeout sesi orang tua (dalam menit).
     * 180 hari = 180 * 24 * 60 = 259200 menit
     */
    private const SESSION_TIMEOUT_MINUTES = 259200;

    public function index()
    {
        // If already logged in as parent, redirect to dashboard
        if (session()->has('parent_student_id')) {
            // Check session expiry
            if ($this->isSessionExpired()) {
                $this->clearParentSession();
                return view('parent.index')->withErrors(['parent_code' => 'Sesi Anda telah berakhir. Silakan masukkan kode akses kembali.']);
            }
            return redirect()->route('parent.dashboard');
        }
        return view('parent.index');
    }

    public function access(Request $request)
    {
        $request->validate([
            'parent_code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->parent_code));

        $student = Student::where('parent_code', $code)->first();

        if (!$student) {
            // Log failed access attempt for security monitoring
            Log::warning('Parent portal: failed access attempt', [
                'code_hash' => $this->codeHash($code),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => 'access',
            ]);

            return back()->withErrors(['parent_code' => 'Kode akses orang tua tidak valid atau tidak ditemukan.']);
        }

        // Log successful access
        Log::info('Parent portal: successful access', [
            'student_id' => $student->id,
            'ip' => $request->ip(),
            'endpoint' => 'access',
        ]);

        session([
            'parent_student_id' => $student->id,
            'parent_last_activity' => now()->timestamp,
        ]);

        return redirect()->route('parent.dashboard')->with('success', 'Berhasil masuk ke dashboard pemantauan orang tua.');
    }

    /**
     * Show confirmation page for direct link access.
     * Does NOT set session - requires explicit POST confirmation.
     */
    public function viewDirect($code)
    {
        $code = strtoupper(trim($code));
        $student = Student::where('parent_code', $code)->first();

        if (!$student) {
            Log::warning('Parent portal: invalid direct link access', [
                'code_hash' => $this->codeHash($code),
                'ip' => request()->ip(),
                'endpoint' => 'view',
            ]);

            return redirect()->route('parent.index')->withErrors(['parent_code' => 'Link akses tidak valid.']);
        }

        // Show confirmation page instead of directly granting access
        return view('parent.confirm', [
            'student_name' => $student->user->name,
            'code' => $code,
        ]);
    }

    /**
     * Process confirmed direct link access (POST only).
     */
    public function viewDirectConfirm(Request $request)
    {
        $request->validate([
            'parent_code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->parent_code));
        $student = Student::where('parent_code', $code)->first();

        if (!$student) {
            Log::warning('Parent portal: invalid direct link confirm', [
                'code_hash' => $this->codeHash($code),
                'ip' => $request->ip(),
                'endpoint' => 'view.confirm',
            ]);

            return redirect()->route('parent.index')->withErrors(['parent_code' => 'Link akses tidak valid.']);
        }

        // Log successful access
        Log::info('Parent portal: successful direct link access', [
            'student_id' => $student->id,
            'ip' => $request->ip(),
            'endpoint' => 'view.confirm',
        ]);

        session([
            'parent_student_id' => $student->id,
            'parent_last_activity' => now()->timestamp,
        ]);

        return redirect()->route('parent.dashboard');
    }

    public function dashboard()
    {
        $studentId = session('parent_student_id');
        
        if (!$studentId) {
            return redirect()->route('parent.index')->withErrors(['parent_code' => 'Silakan masukkan kode akses terlebih dahulu.']);
        }

        // Check session expiry
        if ($this->isSessionExpired()) {
            $this->clearParentSession();
            return redirect()->route('parent.index')->withErrors(['parent_code' => 'Sesi Anda telah berakhir (tidak aktif selama ' . self::SESSION_TIMEOUT_MINUTES . ' menit). Silakan masukkan kode akses kembali.']);
        }

        // Update last activity timestamp
        session(['parent_last_activity' => now()->timestamp]);

        $student = Student::with(['user', 'schoolClass.academicYear'])->findOrFail($studentId);

        // Daily Attendance summary calculations
        $totDaily = ClassAttendanceDetail::where('student_id', $studentId)->count();
        $hadirDaily = ClassAttendanceDetail::where('student_id', $studentId)->where('status', 'hadir')->count();

        // Assignment Submissions summary calculations
        $completedTasks = AssignmentSubmission::where('student_id', $studentId)->count();
        $gradedSubmissionsQuery = AssignmentSubmission::where('student_id', $studentId)->whereNotNull('score');
        $gradedTasks = $gradedSubmissionsQuery->count();
        $avgScore = $gradedTasks > 0 ? round($gradedSubmissionsQuery->avg('score')) : '-';

        // Behavior Records summary calculations
        $goodBehaviors = BehaviorRecord::where('student_id', $studentId)->where('type', 'positif')->count();
        $badBehaviors = BehaviorRecord::where('student_id', $studentId)->where('type', 'negatif')->count();
        $totalBehaviors = BehaviorRecord::where('student_id', $studentId)->count();

        // Daily Attendance (Wali Kelas) - Paginated 10 items
        $dailyAttendances = ClassAttendanceDetail::where('student_id', $studentId)
            ->join('class_attendances', 'class_attendance_details.class_attendance_id', '=', 'class_attendances.id')
            ->select('class_attendance_details.*')
            ->orderBy('class_attendances.date', 'desc')
            ->with('attendance')
            ->paginate(10, ['*'], 'daily_page')
            ->withQueryString();

        // Subject Attendance - Paginated 10 items
        $subjectAttendances = AttendanceDetail::where('student_id', $studentId)
            ->join('attendances', 'attendance_details.attendance_id', '=', 'attendances.id')
            ->select('attendance_details.*')
            ->orderBy('attendances.date', 'desc')
            ->with(['attendance.subject', 'attendance.teacher.user'])
            ->paginate(10, ['*'], 'subject_page')
            ->withQueryString();

        // Grades (Rapor / Input Nilai) - Paginated 10 items
        $grades = StudentGrade::where('student_id', $studentId)
            ->with(['subject', 'class'])
            ->latest()
            ->paginate(10, ['*'], 'grade_page')
            ->withQueryString();

        // Assignment Submissions (Tugas & Latihan) - Paginated 10 items
        $submissions = AssignmentSubmission::where('student_id', $studentId)
            ->with('assignment.subject')
            ->latest()
            ->paginate(10, ['*'], 'sub_page')
            ->withQueryString();

        // Behavior Records - Paginated 6 items
        $behaviorRecords = BehaviorRecord::where('student_id', $studentId)
            ->latest()
            ->paginate(6, ['*'], 'behavior_page')
            ->withQueryString();

        return view('parent.dashboard', compact(
            'student',
            'totDaily',
            'hadirDaily',
            'completedTasks',
            'gradedTasks',
            'avgScore',
            'goodBehaviors',
            'badBehaviors',
            'totalBehaviors',
            'dailyAttendances',
            'subjectAttendances',
            'grades',
            'submissions',
            'behaviorRecords'
        ));
    }

    public function logout()
    {
        $this->clearParentSession();
        return redirect()->route('parent.index')->with('success', 'Berhasil keluar dari dashboard pemantauan.');
    }

    /**
     * Regenerate parent access code for a student.
     * Only accessible by authenticated users (guru/tatausaha).
     */
    public function regenerateCode(Student $student)
    {
        $oldCode = $student->parent_code;

        do {
            // ponytail: 6 random alphanumeric characters (no prefix)
            $code = strtoupper(Str::random(6));
        } while (Student::where('parent_code', $code)->exists());

        $student->update(['parent_code' => $code]);

        Log::info('Parent portal: code regenerated', [
            'student_id' => $student->id,
            'old_code' => substr($oldCode ?? '', 0, 6) . '****',
            'new_code' => substr($code, 0, 6) . '****',
            'by_user' => auth()->id(),
        ]);

        return back()->with('success', 'Kode akses orang tua berhasil diperbarui.');
    }

    /**
     * Reveal parent access code to authorized staff in a separate page.
     */
    public function revealCode(Student $student)
    {
        $user = auth()->user();

        abort_unless($user && in_array($user->role, ['admin', 'guru', 'tatausaha'], true), 403);

        if (!$student->parent_code) {
            abort(404);
        }

        Log::info('Parent portal: code revealed to staff', [
            'student_id' => $student->id,
            'by_user' => $user->id,
            'by_role' => $user->role,
            'ip' => request()->ip(),
        ]);

        return view('parent.reveal', [
            'student' => $student->load('user'),
            'code' => $student->parent_code,
        ]);
    }
    /**
     * Check if parent session has expired due to inactivity.
     */
    private function isSessionExpired(): bool
    {
        $lastActivity = session('parent_last_activity');
        if (!$lastActivity) {
            return true;
        }

        $timeoutSeconds = self::SESSION_TIMEOUT_MINUTES * 60;
        return (now()->timestamp - $lastActivity) > $timeoutSeconds;
    }

    /**
     * Clear all parent-related session data.
     */
    private function clearParentSession(): void
    {
        session()->forget(['parent_student_id', 'parent_last_activity']);
    }

    private function codeHash(string $code): string
    {
        if ($code === '') {
            return 'empty';
        }

        return hash('sha256', $code);
    }
}
