@extends('layouts.lms')

@section('title', 'Tambah User')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary-theme btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div>
            <h1 class="mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.5rem;">👤 Tambah User Baru</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Buat akun pengguna baru untuk sistem LMS</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">👤</div>
                    <h5 class="content-card-title">Form Tambah User</h5>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <!-- Nama User -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">👤 Nama Lengkap</label>
                            <input class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">📧 Email</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="example@school.com" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">🔐 Peran (Role)</label>
                            <select class="form-select" id="roleSelect" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach(['admin' => '📄 Admin', 'tatausaha' => '🃋 Tata Usaha', 'guru' => '👨‍🏫 Guru', 'siswa' => '🎒 Siswa'] as $key => $label)
                                    <option value="{{ $key }}" @selected(old('role') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- NIP (untuk guru) -->
                        <div class="mb-4" id="nipField" style="display: none;">
                            <label class="form-label fw-semibold">🆔 NIP (Nomor Induk Pegawai)</label>
                            <input class="form-control" type="text" name="nip" value="{{ old('nip') }}" placeholder="Masukkan NIP guru">
                            @error('nip')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- NISN (untuk siswa) -->
                        <div class="mb-4" id="nisnField" style="display: none;">
                            <label class="form-label fw-semibold">🆔 NISN (Nomor Induk Siswa Nasional)</label>
                            <input class="form-control" type="text" name="nisn" value="{{ old('nisn') }}" placeholder="Masukkan NISN siswa">
                            @error('nisn')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password Fields -->
                        <div class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">🔄 Password</label>
                                    <x-text-input id="admin_create_password" class="form-control" type="password" name="password" placeholder="Masukkan password" required />
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">✅ Konfirmasi Password</label>
                                    <x-text-input id="admin_create_password_confirmation" class="form-control" type="password" name="password_confirmation" placeholder="Ulangi password" required />
                                    @error('password_confirmation')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <x-password-strength-meter inputId="admin_create_password" confirmInputId="admin_create_password_confirmation" />
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-lg btn-primary" type="submit">✓ Buat User</button>
                            <a class="btn btn-lg btn-outline-secondary-theme" href="{{ route('admin.users.index') }}">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-5">
            <div class="content-card" style="border-top: 4px solid var(--accent);">
                <div class="content-card-header">
                    <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.06)); color: var(--accent);">💡</div>
                    <h5 class="content-card-title">Panduan Role</h5>
                </div>
                <div class="content-card-body">
                    <ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
                        <li class="mb-3">
                            <strong style="color: var(--primary);">📄 Admin</strong><br>
                            Akses penuh ke semua fitur sistem
                        </li>
                        <li class="mb-3">
                            <strong style="color: var(--secondary);">🃋 Tata Usaha</strong><br>
                            Mengelola data siswa, guru, dan kelas
                        </li>
                        <li class="mb-3">
                            <strong style="color: var(--primary);">👨‍🏫 Guru</strong><br>
                            Membuat tugas, materi, dan penilaian
                        </li>
                        <li>
                            <strong style="color: var(--accent);">🎒 Siswa</strong><br>
                            Mengumpulkan tugas dan melihat nilai
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('roleSelect').addEventListener('change', function() {
            const role = this.value;
            document.getElementById('nipField').style.display = (role === 'guru' || role === 'tatausaha' || role === 'admin') ? 'block' : 'none';
            document.getElementById('nisnField').style.display = role === 'siswa' ? 'block' : 'none';
            
            // Reset values when toggling
            if (role !== 'guru' && role !== 'tatausaha' && role !== 'admin') {
                const nipInput = document.querySelector('input[name="nip"]');
                if (nipInput) nipInput.value = '';
            }
            if (role !== 'siswa') {
                const nisnInput = document.querySelector('input[name="nisn"]');
                if (nisnInput) nisnInput.value = '';
            }
        });
        
        // Show field jika sudah ada nilai sebelumnya (reload form dengan error)
        window.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('roleSelect');
            if (['guru', 'tatausaha', 'admin'].includes(roleSelect.value)) {
                document.getElementById('nipField').style.display = 'block';
            } else if (roleSelect.value === 'siswa') {
                document.getElementById('nisnField').style.display = 'block';
            }
        });
    </script>
@endsection
