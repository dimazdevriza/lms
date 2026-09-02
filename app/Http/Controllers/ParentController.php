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
     * Nama cookie persisten untuk mengingat sesi login orang tua di perangkat/browser.
     */
    public const COOKIE_NAME = 'parent_access_token';

    /**
     * Durasi masa aktif cookie (dalam menit).
     * 1 tahun = 365 * 24 * 60 = 525600 menit (berlaku sepanjang tahun ajaran).
     */
    public const COOKIE_LIFETIME_MINUTES = 525600;

    public function index(Request $request)
    {
        // Cek apakah orang tua sudah pernah login dan kodenya masih berlaku
        $student = $this->resolveAuthenticatedStudent($request);
        if ($student) {
            return redirect()->route('parent.dashboard');
        }

        // Jika terdapat cookie lama tetapi kodenya sudah berubah (misal tahun ajaran baru / kode di-regenerate)
        if ($request->hasCookie(self::COOKIE_NAME)) {
            $this->clearParentSession();
            return view('parent.index')->withErrors([
                'parent_code' => 'Kode akses orang tua telah diperbarui (tahun ajaran baru). Silakan masukkan kode akses terbaru dari pihak sekolah.'
            ]);
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

        $this->setParentAuth($student);

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

        $this->setParentAuth($student);

        return redirect()->route('parent.dashboard');
    }

    public function dashboard(Request $request)
    {
        $student = $this->resolveAuthenticatedStudent($request);

        if (!$student) {
            $msg = $request->hasCookie(self::COOKIE_NAME)
                ? 'Kode akses orang tua telah diperbarui (tahun ajaran baru). Silakan masukkan kode akses terbaru.'
                : 'Silakan masukkan kode akses terlebih dahulu.';

            $this->clearParentSession();
            return redirect()->route('parent.index')->withErrors(['parent_code' => $msg]);
        }

        $studentId = $student->id;
        $student->load(['user', 'schoolClass.academicYear']);

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
     * Regenerate parent access code for a single student.
     * Only accessible by authenticated users (guru/tatausaha/admin).
     */
    public function regenerateCode(Student $student)
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'guru', 'tatausaha'], true), 403);

        $oldCode = $student->parent_code;

        do {
            $code = strtoupper(Str::random(6));
        } while (Student::where('parent_code', $code)->exists());

        $student->update(['parent_code' => $code]);

        Log::info('Parent portal: code regenerated', [
            'student_id' => $student->id,
            'old_code' => substr($oldCode ?? '', 0, 6) . '****',
            'new_code' => substr($code, 0, 6) . '****',
            'by_user' => auth()->id(),
        ]);

        return back()->with('success', 'Kode akses orang tua berhasil diperbarui. Akses orang tua lama otomatis direset dan memerlukan kode baru ini.');
    }

    /**
     * Regenerate parent access code for ALL students (misal saat Tahun Ajaran Baru).
     * Only accessible by admin / tatausaha.
     */
    public function regenerateAllCodes(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'tatausaha'], true), 403);

        $count = 0;
        Student::chunk(100, function ($students) use (&$count) {
            foreach ($students as $student) {
                do {
                    $code = strtoupper(Str::random(6));
                } while (Student::where('parent_code', $code)->exists());

                $student->update(['parent_code' => $code]);
                $count++;
            }
        });

        Log::info('Parent portal: all codes regenerated for new academic year', [
            'total_updated' => $count,
            'by_user' => $user->id,
            'by_role' => $user->role,
        ]);

        return back()->with('success', "Berhasil memperbarui kode akses orang tua untuk {$count} siswa (Tahun Ajaran Baru). Seluruh sesi akses sebelumnya otomatis direset.");
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
     * Set session and persistent cookie on parent device.
     */
    private function setParentAuth(Student $student): void
    {
        $token = $this->makeCodeToken($student);

        session([
            'parent_student_id' => $student->id,
            'parent_code_hash' => $token,
            'parent_last_activity' => now()->timestamp,
        ]);

        cookie()->queue(
            cookie()->make(
                self::COOKIE_NAME,
                $student->id . '|' . $token,
                self::COOKIE_LIFETIME_MINUTES,
                null,
                null,
                false,
                true // HTTP-Only
            )
        );
    }

    /**
     * Resolve authenticated student from session or persistent device cookie.
     * If the student's code has changed in the database, this returns null and invalidates the session/cookie.
     */
    private function resolveAuthenticatedStudent(Request $request): ?Student
    {
        // 1. Periksa Session aktif
        if (session()->has('parent_student_id') && session()->has('parent_code_hash')) {
            $student = Student::find(session('parent_student_id'));
            if ($student && !empty($student->parent_code) && hash_equals($this->makeCodeToken($student), (string) session('parent_code_hash'))) {
                session(['parent_last_activity' => now()->timestamp]);
                return $student;
            }
            // Kode di database telah berubah atau siswa tidak ditemukan
            $this->clearParentSession();
        }

        // 2. Periksa Persistent Device Cookie jika session kosong/baru
        $cookieVal = $request->cookie(self::COOKIE_NAME);
        if ($cookieVal && is_string($cookieVal) && str_contains($cookieVal, '|')) {
            [$studentId, $cookieHash] = explode('|', $cookieVal, 2);
            $student = Student::find($studentId);

            if ($student && !empty($student->parent_code) && hash_equals($this->makeCodeToken($student), (string) $cookieHash)) {
                $this->setParentAuth($student);
                return $student;
            }

            // Kode di database telah diubah (misal Tahun Ajaran Baru)
            $this->clearParentSession();
        }

        return null;
    }

    /**
     * Generate secure HMAC token tied to student ID and parent code.
     */
    private function makeCodeToken(Student $student): string
    {
        $secret = config('app.key') ?: 'lms_secret_key';
        return hash_hmac('sha256', $student->id . ':' . ($student->parent_code ?? ''), $secret);
    }

    /**
     * Clear all parent-related session and cookie data.
     */
    private function clearParentSession(): void
    {
        session()->forget(['parent_student_id', 'parent_code_hash', 'parent_last_activity']);
        cookie()->queue(cookie()->forget(self::COOKIE_NAME));
    }

    private function codeHash(string $code): string
    {
        if ($code === '') {
            return 'empty';
        }

        return hash('sha256', $code);
    }
}
