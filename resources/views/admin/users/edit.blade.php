@extends('layouts.lms')

@section('title', 'Edit User')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary-theme btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.5rem;">✏️ Edit User</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Perbarui informasi pengguna</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">👤</div>
                    <h5 class="content-card-title">Form Edit User</h5>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">👤 Nama</label>
                            <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">📧 Email</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">🔐 Role</label>
                            <select class="form-select" id="roleSelect" name="role" required>
                                @foreach(['admin' => 'Admin', 'tatausaha' => 'Tata Usaha', 'guru' => 'Guru', 'siswa' => 'Siswa'] as $key => $label)
                                    <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if(in_array($user->role, ['guru', 'tatausaha', 'admin']))
                        <div class="mb-4" id="nipField">
                            <label class="form-label fw-semibold">🆔 NIP (Nomor Induk Pegawai)</label>
                            <input class="form-control" type="text" name="nip" value="{{ old('nip', $user->teacher?->nip ?? $user->adminStaff?->nip) }}" placeholder="Masukkan NIP">
                            @error('nip')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        @endif

                        @if($user->role === 'siswa')
                        <div class="mb-4" id="nisnField">
                            <label class="form-label fw-semibold">🆔 NISN (Nomor Induk Siswa Nasional)</label>
                            <input class="form-control" type="text" name="nisn" value="{{ old('nisn', $user->student?->nisn) }}" placeholder="Masukkan NISN siswa">
                            @error('nisn')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">🔄 Password Baru (opsional)</label>
                                    <x-text-input id="admin_edit_password" class="form-control" type="password" name="password" placeholder="Kosongkan jika tidak diubah" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">✅ Konfirmasi Password Baru</label>
                                    <x-text-input id="admin_edit_password_confirmation" class="form-control" type="password" name="password_confirmation" placeholder="Konfirmasi password baru" />
                                </div>
                            </div>
                            <x-password-strength-meter inputId="admin_edit_password" confirmInputId="admin_edit_password_confirmation" />
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit">✓ Update User</button>
                            <a class="btn btn-outline-secondary-theme" href="{{ route('admin.users.index') }}">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('roleSelect').addEventListener('change', function() {
            const role = this.value;
            const nipField = document.getElementById('nipField');
            const nisnField = document.getElementById('nisnField');
            
            if (nipField) nipField.style.display = role === 'guru' ? 'block' : 'none';
            if (nisnField) nisnField.style.display = role === 'siswa' ? 'block' : 'none';
        });
    </script>
@endsection
