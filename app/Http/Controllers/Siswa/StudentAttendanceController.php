<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDetail;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    public function index(): View
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $records = AttendanceDetail::with(['attendance'])
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(10);

        return view('siswa.attendance.index', compact('records'));
    }
}

