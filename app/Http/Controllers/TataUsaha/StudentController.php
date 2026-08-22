<?php

namespace App\Http\Controllers\TataUsaha;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with(['user', 'schoolClass'])
            ->select('students.*')
            ->join('users', 'users.id', '=', 'students.user_id');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('students.nisn', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Filter Jurusan
        if ($request->has('major') && $request->major != '') {
            $query->whereHas('schoolClass', function($q) use ($request) {
                $q->where('major', $request->major);
            });
        }

        // Filter Kelas
        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('students.class_id', $request->class_id);
        }

        // Sort
        $sort = $request->input('sort', 'name_asc');
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('users.name', 'desc');
                break;
            case 'latest':
                $query->orderBy('students.created_at', 'desc');
                break;
            case 'earliest':
                $query->orderBy('students.created_at', 'asc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('users.name', 'asc');
                break;
        }

        $students = $query->paginate(10)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('tatausaha.students.index', compact('students', 'classes', 'sort'));
    }

    public function create(): View
    {
        $classes = SchoolClass::orderBy('name')->get();

        return view('tatausaha.students.create', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nisn' => ['required', 'string', 'max:50', 'unique:students,nisn'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $tempPassword = Str::random(8);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($tempPassword),
        ]);
        $user->role = 'siswa';
        $user->save();

        Student::create([
            'user_id' => $user->id,
            'nisn' => $data['nisn'],
            'class_id' => $data['class_id'] ?? null,
        ]);

        return redirect()->route('tatausaha.students.index')
            ->with('success', "Data siswa berhasil dibuat. Password sementara: {$tempPassword} — minta siswa segera mengganti password.");
    }

    public function edit(Student $student): View
    {
        $classes = SchoolClass::orderBy('name')->get();

        return view('tatausaha.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $student->user_id],
            'nisn' => ['required', 'string', 'max:50', 'unique:students,nisn,' . $student->id],
            'class_id' => ['nullable', 'exists:classes,id'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()],
        ]);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $userData['password'] = bcrypt($data['password']);
        }

        $student->user->update($userData);

        $student->update([
            'nisn' => $data['nisn'],
            'class_id' => $data['class_id'] ?? null,
        ]);

        return redirect()->route('tatausaha.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        // User will be deleted automatically if cascade delete is set, but better be safe
        $user = $student->user;
        $student->delete();
        $user->delete();

        return redirect()->route('tatausaha.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }
}

