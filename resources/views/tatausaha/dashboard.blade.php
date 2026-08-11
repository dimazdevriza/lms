@extends('layouts.lms')

@section('title', 'Dashboard Tata Usaha')

@push('styles')
<style>
    .tu-header-banner {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: var(--radius-lg, 20px);
        padding: 36px 36px 28px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
        color: #fff;
    }
    .tu-header-banner::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 260px;
        height: 260px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .tu-header-banner::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 30%;
        width: 180px;
        height: 180px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .tu-header-banner h1 {
        color: #fff !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-weight: 800;
        font-size: 1.75rem;
        margin-bottom: 6px;
        position: relative;
        z-index: 1;
    }
    .tu-header-banner p {
        opacity: 0.85;
        font-size: 0.95rem;
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }
    .tu-header-banner .header-date {
        opacity: 0.7;
        font-size: 0.85rem;
        position: relative;
        z-index: 1;
    }
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        border-radius: var(--radius-md, 14px);
        border: 2px solid rgba(27, 94, 32, 0.08);
        background: var(--bg-card, #fff);
        color: var(--primary, #1B5E20);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
    }
    .quick-action-btn:hover {
        border-color: var(--primary, #1B5E20);
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(37, 103, 30, 0.10);
        color: var(--primary, #1B5E20);
    }
    .quick-action-btn .action-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .reminder-item {
        padding: 14px 16px;
        border-radius: var(--radius-sm, 10px);
        margin-bottom: 10px;
        border-left: 3px solid;
    }
    .reminder-item--warning {
        background: rgba(249, 168, 37, 0.06);
        border-left-color: var(--accent, #F9A825);
    }
    .reminder-item--info {
        background: rgba(27, 94, 32, 0.04);
        border-left-color: var(--primary, #1B5E20);
    }
    .reminder-item--success {
        background: rgba(67, 160, 71, 0.06);
        border-left-color: var(--secondary, #43A047);
    }
    .activity-item {
        padding: 14px 0;
        border-bottom: 1px solid rgba(27, 94, 32, 0.06);
    }
    .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
</style>
@endpush

@section('content')
    {{-- Header Banner --}}
    <div class="tu-header-banner reveal">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="fas fa-clipboard-list me-2" style="color: rgba(255,255,255,0.9);"></i> Dashboard Tata Usaha</h1>
                <p>Kelola administrasi sekolah dengan mudah dan terorganisir</p>
            </div>
            <div class="header-date">
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid reveal reveal-delay-1">
        {{-- Total Siswa --}}
        <a href="{{ route('tatausaha.students.index') }}" class="text-decoration-none">
            <div class="stat-card stat-card--attendance">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon-circle stat-icon-circle--green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-label">Total Siswa</div>
                <div class="stat-value stat-value--green">{{ $studentCount ?? 0 }}</div>
                <div class="stat-sub">Siswa terdaftar</div>
            </div>
        </a>

        {{-- Total Guru --}}
        <a href="{{ route('tatausaha.teachers.index') }}" class="text-decoration-none">
            <div class="stat-card stat-card--grades">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon-circle stat-icon-circle--gold">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="stat-label">Total Guru</div>
                <div class="stat-value stat-value--primary">{{ $teacherCount ?? 0 }}</div>
                <div class="stat-sub">Guru aktif</div>
            </div>
        </a>

        {{-- Total Kelas --}}
        <a href="{{ route('tatausaha.reports.index') }}" class="text-decoration-none">
            <div class="stat-card stat-card--behavior">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon-circle stat-icon-circle--deep">
                        <i class="fas fa-school"></i>
                    </div>
                </div>
                <div class="stat-label">Total Kelas</div>
                <div class="stat-value stat-value--primary">{{ $classCount ?? 0 }}</div>
                <div class="stat-sub">Kelas tersedia</div>
            </div>
        </a>

        {{-- Tahun Ajaran --}}
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon-circle stat-icon-circle--green">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <div class="stat-label">Tahun Ajaran</div>
            <div class="stat-value stat-value--green" style="font-size: 1.5rem;">
                {{ $academicYear ? $academicYear->name : 'N/A' }}
            </div>
            <div class="stat-sub">Aktif</div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row">
        {{-- Statistik Kelas --}}
        <div class="col-md-7 mb-4">
            <div class="content-card reveal reveal-delay-2">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h2 class="content-card-title">Statistik Kelas (Per Jurusan)</h2>
                </div>
                <div class="content-card-body">
                    @foreach($classes as $major => $majorClasses)
                        <div class="mb-4">
                            <h6 class="text-uppercase small fw-bold mb-2" style="color: var(--primary); letter-spacing: 0.05em;">
                                <i class="fas fa-layer-group me-1"></i> JURUSAN {{ $major ?? 'UMUM' }}
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kelas</th>
                                            <th class="text-center">Siswa</th>
                                            <th>Wali Kelas</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($majorClasses as $class)
                                            <tr>
                                                <td><strong>{{ $class->name }}</strong></td>
                                                <td class="text-center">
                                                    <span class="status-badge status-badge--hadir">{{ $class->students_count }}</span>
                                                </td>
                                                <td>
                                                    <small>{{ optional(optional($class->homeroomTeacher)->user)->name ?? 'Belum ada' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('tatausaha.students.index', ['class_id' => $class->id]) }}" class="btn btn-sm btn-link p-0" style="color: var(--secondary);">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kegiatan Terbaru --}}
            <div class="content-card reveal reveal-delay-3">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h2 class="content-card-title">Kegiatan Terbaru</h2>
                </div>
                <div class="content-card-body">
                    <div class="activity-item">
                        <div class="d-flex justify-content-between">
                            <strong>Pendaftaran Siswa Baru</strong>
                            <small class="text-muted">2 March 2026</small>
                        </div>
                        <small class="text-muted">5 siswa baru terdaftar</small>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between">
                            <strong>Pembaruan Data Guru</strong>
                            <small class="text-muted">1 March 2026</small>
                        </div>
                        <small class="text-muted">2 guru melakukan update profil</small>
                    </div>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between">
                            <strong>Pengaturan Jadwal</strong>
                            <small class="text-muted">28 Feb 2026</small>
                        </div>
                        <small class="text-muted">Jadwal semester 2 telah diperbarui</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-5 mb-4">
            {{-- Aksi Cepat --}}
            <div class="content-card reveal reveal-delay-2">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h2 class="content-card-title">Aksi Cepat</h2>
                </div>
                <div class="content-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tatausaha.students.index') }}" class="quick-action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg, rgba(27, 94, 32, 0.1), rgba(67, 160, 71, 0.06));">
                                <i class="fas fa-users" style="color: var(--primary);"></i>
                            </div>
                            Kelola Siswa
                        </a>
                        <a href="{{ route('tatausaha.teachers.index') }}" class="quick-action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg, rgba(67, 160, 71, 0.12), rgba(67, 160, 71, 0.06));">
                                <i class="fas fa-chalkboard-teacher" style="color: var(--secondary);"></i>
                            </div>
                            Kelola Guru
                        </a>
                        <a href="{{ route('tatausaha.reports.index') }}" class="quick-action-btn">
                            <div class="action-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.06));">
                                <i class="fas fa-chart-pie" style="color: var(--accent);"></i>
                            </div>
                            Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>

            {{-- Pengingat --}}
            <div class="content-card reveal reveal-delay-3">
                <div class="content-card-header">
                    <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.06)); color: var(--accent);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h2 class="content-card-title">Pengingat</h2>
                </div>
                <div class="content-card-body">
                    <div class="reminder-item reminder-item--warning">
                        <strong><i class="fas fa-calendar-alt me-1 text-warning"></i> UTS Minggu Depan</strong>
                        <p class="mb-0 mt-1 small text-muted">Persiapkan jadwal dan ruang ujian</p>
                    </div>
                    <div class="reminder-item reminder-item--info">
                        <strong><i class="fas fa-edit me-1 text-primary"></i> Input Nilai</strong>
                        <p class="mb-0 mt-1 small text-muted">Guru diminta untuk melengkapi input nilai</p>
                    </div>
                    <div class="reminder-item reminder-item--success">
                        <strong><i class="fas fa-check-circle me-1 text-success"></i> Pembayaran SPP</strong>
                        <p class="mb-0 mt-1 small text-muted">Pengingat: Deadline SPP 10 Maret 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
