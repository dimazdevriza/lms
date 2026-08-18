<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminStaff;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->input('sort', 'name_asc');
        switch ($sort) {
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'earliest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $users = $query->paginate(20)->appends($request->query());

        return view('admin.users.index', compact('users', 'sort'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'role' => ['required', 'in:admin,tatausaha,guru,siswa'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->role = $data['role'];
        $user->save();

        // Buat entry di tabel relasi berdasarkan role
        if ($user->role === 'guru') {
            Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
            ]);
        } elseif ($user->role === 'siswa') {
            Student::create([
                'user_id' => $user->id,
                'nisn' => $data['nisn'] ?? null,
            ]);
        } elseif (in_array($user->role, ['admin', 'tatausaha'])) {
            AdminStaff::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'position' => $user->role,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,tatausaha,guru,siswa'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        // role/nip/nisn bukan bagian dari $fillable User — simpan dulu sebelum di-unset
        $role = $data['role'];
        $nip = $data['nip'] ?? null;
        $nisn = $data['nisn'] ?? null;
        unset($data['role'], $data['nip'], $data['nisn']);

        $user->update($data);
        $user->role = $role;
        $user->save();

        // Update atau buat entry di tabel relasi berdasarkan role
        if ($user->role === 'guru') {
            if ($user->teacher) {
                $user->teacher->update(['nip' => $nip]);
            } else {
                Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $nip,
                ]);
            }
        } elseif ($user->role === 'siswa') {
            if ($user->student) {
                $user->student->update(['nisn' => $nisn]);
            } else {
                Student::create([
                    'user_id' => $user->id,
                    'nisn' => $nisn,
                ]);
            }
        } elseif (in_array($user->role, ['admin', 'tatausaha'])) {
            if ($user->adminStaff) {
                $user->adminStaff->update([
                    'nip' => $nip,
                    'position' => $user->role,
                ]);
            } else {
                AdminStaff::create([
                    'user_id' => $user->id,
                    'nip' => $nip,
                    'position' => $user->role,
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->student) {
            $user->student->delete();
        }
        if ($user->teacher) {
            $user->teacher->delete();
        }
        if ($user->adminStaff) {
            $user->adminStaff->delete();
        }
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}

