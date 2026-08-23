@extends('layouts.lms')

@section('title', 'Data Siswa - ' . $class->name)

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-5 reveal">
        <a href="{{ route('guru.classroom.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('guru.classroom.index') }}" style="color: var(--primary-light); text-decoration: none;">Kelas Saya</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data Siswa</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary);">Data Siswa Kelas {{ $class->name }}</h1>
            <p class="text-muted mb-0">Total terdaftar: <strong>{{ $students->count() }} siswa</strong></p>
        </div>
    <!-- Summary Stat Cards -->
    <div class="row g-3 mb-4 reveal">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" style="background-color: rgba(27, 94, 32, 0.1); color: var(--primary);">
                    <i class="fas fa-users fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small">Total Siswa</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $students->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                    <i class="fas fa-user-check fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small">Kehadiran Kelas</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $classAttendanceAvg }}%</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" style="background-color: rgba(255, 193, 7, 0.15); color: #b26a00;">
                    <i class="fas fa-chart-line fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small">Rata-Rata Nilai</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $classGradeAvg ? $classGradeAvg : '—' }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded-3 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                    <i class="fas fa-award fa-lg"></i>
                </div>
                <div>
                    <span class="text-muted small">Catatan Perilaku</span>
                    <h4 class="fw-bold mb-0 text-dark"><span class="text-success">+{{ $behaviorCountPositif ?? 0 }}</span> / <span class="text-danger">-{{ $behaviorCountNegatif ?? 0 }}</span></h4>
                </div>
            </div>
        </div>
    </div>

    @if($students->count() > 0)
        <div class="content-card reveal-delay-1">
            <div class="content-card-header bg-white py-3">
                <div class="content-card-header-icon" style="background-color: rgba(27, 94, 32, 0.08); color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="content-card-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;">Daftar Siswa Aktif</h5>
            </div>
            <div class="content-card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; border-bottom: 1px solid rgba(37, 103, 30, 0.08);">No.</th>
                                <th class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; border-bottom: 1px solid rgba(37, 103, 30, 0.08);">🆔 NISN</th>
                                <th class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; border-bottom: 1px solid rgba(37, 103, 30, 0.08);">👤 Nama</th>
                                <th class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; border-bottom: 1px solid rgba(37, 103, 30, 0.08);">📞 No. HP</th>
                                <th class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; border-bottom: 1px solid rgba(37, 103, 30, 0.08);">🔑 Akses Ortu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td><strong class="text-dark">{{ $index + 1 }}</strong></td>
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fw-bold" style="border-radius: 8px; font-size: 0.75rem;">
                                            {{ $student->nisn }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $student->user?->name ?? 'Siswa (Tanpa Nama)' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size: 0.85rem;">{{ $student->phone ?? '-' }}</span>
                                    </td>
                                     <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($student->parent_code)
                                                <code class="bg-light px-2 py-1 rounded text-dark fw-bold border" style="font-size: 0.8rem; border-radius: 6px;">{{ substr($student->parent_code, 0, 2) . str_repeat('*', max(strlen($student->parent_code) - 2, 4)) }}</code>
                                                @if(!empty($isHomeroomTeacher) || auth()->user()->role === 'admin')
                                                    <form action="{{ route('parent.code.reveal', $student) }}" method="POST" class="d-inline" target="_blank">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm p-1 border-0 text-primary" title="Lihat Kode di Tab Baru" style="background: transparent;">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                            @if(!empty($isHomeroomTeacher) || auth()->user()->role === 'admin')
                                                <form action="{{ route('parent.code.regenerate', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $student->parent_code ? 'Kode akses lama akan tidak berlaku lagi. Lanjutkan?' : 'Buat kode akses baru. Lanjutkan?' }}')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm p-1 border-0 text-warning" title="{{ $student->parent_code ? 'Perbarui Kode Ortu' : 'Buat Kode Ortu' }}">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info border-0 shadow-sm p-4 reveal" role="alert" style="border-radius: 12px; background-color: rgba(67, 160, 71, 0.08); color: var(--primary);">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-info-circle fa-2x"></i>
                <div>
                    <h5 class="fw-bold mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">Belum Ada Siswa</h5>
                    <p class="mb-0 small text-muted">Belum ada data siswa terdaftar di dalam kelas ini.</p>
                </div>
            </div>
        </div>
    @endif
@endsection
