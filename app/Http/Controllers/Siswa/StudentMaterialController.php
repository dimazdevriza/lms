<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\Material;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Meeting;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentMaterialController extends Controller
{
    /**
     * Menampilkan daftar Mata Pelajaran yang tersedia untuk kelas siswa.
     * Berdasarkan penugasan guru mengambil kelas (class_subject_teacher).
     * Fallback: jika belum ada penugasan, ambil dari meeting/material yang sudah ada.
     */
    public function subjects(): View
    {
        $student = Student::where('user_id', Auth::id())->first();
        abort_unless($student, 403, 'Profil siswa tidak ditemukan.');

        // Prioritas 1: Mata pelajaran dari penugasan guru mengambil kelas
        $subjectIdsFromAssignments = ClassSubjectTeacher::where('class_id', $student->class_id)
            ->pluck('subject_id')
            ->unique();

        // Prioritas 2 (fallback): Jika belum ada penugasan, ambil dari meeting/material
        if ($subjectIdsFromAssignments->isEmpty()) {
            $subjectIdsFromMeetings = Meeting::where('class_id', $student->class_id)->where('is_visible', true)->pluck('subject_id');
            $subjectIdsFromMaterials = Material::where('class_id', $student->class_id)
                ->where(function ($query) {
                    $query->whereNull('meeting_id')
                        ->orWhereHas('meeting', function ($q) {
                            $q->where('is_visible', true);
                        });
                })
                ->pluck('subject_id');
            $subjectIdsFromAssignments = $subjectIdsFromMeetings->merge($subjectIdsFromMaterials)->unique();
        }

        $subjects = Subject::whereIn('id', $subjectIdsFromAssignments)
            ->orderBy('name')
            ->get();

        return view('siswa.subjects.index', compact('subjects', 'student'));
    }

    /**
     * Menampilkan daftar Guru (Pengajar) untuk Mata Pelajaran tertentu
     */
    public function subjectTeachers(Subject $subject): View
    {
        $student = Student::where('user_id', Auth::id())->with('schoolClass')->first();
        abort_unless($student, 403);

        // Ambil semua guru yang mengajar mapel ini di kelas ini
        $teacherIdsFromAssignments = ClassSubjectTeacher::where('class_id', $student->class_id)
            ->where('subject_id', $subject->id)
            ->pluck('teacher_id')
            ->unique();
            
        // Fallback jika belum ada di assignment tapi ada di meeting
        if ($teacherIdsFromAssignments->isEmpty()) {
            $teacherIdsFromMeetings = Meeting::where('class_id', $student->class_id)
                ->where('subject_id', $subject->id)
                ->where('is_visible', true)
                ->pluck('teacher_id')
                ->unique();
            $teacherIdsFromAssignments = $teacherIdsFromMeetings;
        }

        $teachers = Teacher::with('user')->whereIn('id', $teacherIdsFromAssignments)->get();

        return view('siswa.subjects.teachers', compact('subject', 'teachers', 'student'));
    }

    /**
     * Menampilkan daftar Pertemuan untuk Guru dan Mata Pelajaran tertentu
     */
    public function teacherMeetings(Subject $subject, Teacher $teacher): View
    {
        $student = Student::where('user_id', Auth::id())->with('schoolClass')->first();
        abort_unless($student, 403);

        $meetings = Meeting::where('class_id', $student->class_id)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', $teacher->id)
            ->where('is_visible', true)
            ->with(['teacher.user'])
            ->orderBy('number', 'desc')
            ->get();

        // Juga ambil materi mandiri (tanpa pertemuan) untuk mapel ini dari guru ini
        $standaloneMaterials = Material::where('class_id', $student->class_id)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', $teacher->id)
            ->whereNull('meeting_id')
            ->get();

        return view('siswa.subjects.show', compact('subject', 'teacher', 'meetings', 'standaloneMaterials', 'student'));
    }

    /**
     * Menampilkan detail satu Pertemuan (Materi & Tugas)
     */
    public function meetingDetail(Meeting $meeting): View
    {
        $student = Student::where('user_id', Auth::id())->first();
        abort_unless($student, 403);
        
        // Pastikan pertemuan ini memang untuk kelas si siswa
        abort_if($meeting->class_id !== $student->class_id, 403);
        abort_unless($meeting->is_visible, 404);

        $meeting->load(['materials', 'assignments', 'subject', 'teacher.user']);

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

        return view('siswa.meetings.show', compact('meeting', 'student', 'discussionPosts'));
    }

    /**
     * Menampilkan detail Materi (untuk melihat PDF & YouTube)
     */
    public function show(Material $material): View
    {
        $student = Student::where('user_id', Auth::id())->first();
        abort_unless($student, 403);
        
        abort_if($material->class_id !== $student->class_id, 403);
        if ($material->meeting_id) {
            abort_unless($material->meeting?->is_visible, 404);
        }

        return view('siswa.materials.show', compact('material', 'student'));
    }
}
