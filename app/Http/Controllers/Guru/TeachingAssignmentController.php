<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubjectTeacher;
use App\Models\AcademicYear;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeachingAssignmentController extends Controller
{
    /**
     * Tampilkan form untuk menambah kelas mandiri (ad-hoc)
     */
    public function createMandiri()
    {
        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('guru.meetings.create-mandiri', compact('classes', 'subjects'));
    }

    /**
     * Simpan penugasan kelas mandiri (ad-hoc)
     */
    public function storeMandiri(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif. Silakan hubungi Administrator.');
        }

        // Cek apakah guru ini sudah ditugaskan mengajar mapel ini di kelas tersebut
        $exists = ClassSubjectTeacher::where('academic_year_id', $activeYear->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if ($exists) {
            return back()->with('error', 'Anda sudah ditugaskan mengajar mapel ini di kelas tersebut.');
        }

        // Simpan sebagai kelas mandiri
        ClassSubjectTeacher::create([
            'academic_year_id' => $activeYear->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $teacher->id,
            'is_mandiri' => true,
        ]);

        return redirect()->route('guru.meetings.index')
            ->with('success', 'Kelas Mandiri berhasil ditambahkan. Anda sekarang dapat membuat materi dan tugas untuk kelas tersebut.');
    }

    public function destroyMandiri(string $id)
    {
        $teacherId = Teacher::where('user_id', Auth::id())->value('id');
        abort_unless($teacherId, 403);

        $assignment = ClassSubjectTeacher::where('id', $id)
            ->where('teacher_id', $teacherId)
            ->where('is_mandiri', true)
            ->firstOrFail();

        $assignment->delete();

        return back()->with('success', 'Kelas Mandiri berhasil dihapus.');
    }
}
