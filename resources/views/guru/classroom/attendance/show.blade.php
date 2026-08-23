@extends('layouts.lms')

@section('title', 'Detail Absensi Kelas - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">🔍 Detail Absensi Harian</h1>
                    <p>Kelas {{ $class->name }} • {{ $attendance->date->translatedFormat('d F Y') }}</p>
                </div>
                <a href="{{ route('guru.classroom.attendance', $class) }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @php
        $stats = $attendance->details->groupBy('status')->map->count();
    @endphp

    <div class="stats-grid reveal reveal-delay-1">
        <!-- Hadir -->
        <div class="stat-card stat-card--attendance">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-circle stat-icon-circle--green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background-color: rgba(67, 160, 71, 0.12); color: #2E7D32;">HADIR</span>
            </div>
            <div class="stat-label text-uppercase fw-semibold">Total Hadir</div>
            <div class="stat-value stat-value--green">{{ $stats['hadir'] ?? 0 }}</div>
            <div class="stat-sub text-muted">Siswa hadir kelas</div>
        </div>

        <!-- Izin -->
        <div class="stat-card stat-card--grades">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-circle stat-icon-circle--gold">
                    <i class="fas fa-envelope"></i>
                </div>
                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background-color: rgba(249, 168, 37, 0.12); color: #B26A00;">IZIN</span>
            </div>
            <div class="stat-label text-uppercase fw-semibold">Total Izin</div>
            <div class="stat-value" style="color: #B26A00;">{{ $stats['izin'] ?? 0 }}</div>
            <div class="stat-sub text-muted">Siswa berkirim surat</div>
        </div>

        <!-- Sakit -->
        <div class="stat-card stat-card--grades">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-circle" style="background: rgba(255, 152, 0, 0.12); color: #E65100; width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-medkit"></i>
                </div>
                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background-color: rgba(255, 152, 0, 0.12); color: #E65100;">SAKIT</span>
            </div>
            <div class="stat-label text-uppercase fw-semibold">Total Sakit</div>
            <div class="stat-value" style="color: #E65100;">{{ $stats['sakit'] ?? 0 }}</div>
            <div class="stat-sub text-muted">Siswa berhalangan sakit</div>
        </div>

        <!-- Alpa -->
        <div class="stat-card" style="background: #FFF5F5; border-color: rgba(198, 40, 40, 0.1);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-circle" style="background: rgba(198, 40, 40, 0.12); color: #C62828; width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background-color: rgba(198, 40, 40, 0.12); color: #C62828;">ALPA</span>
            </div>
            <div class="stat-label text-uppercase fw-semibold">Total Alpa</div>
            <div class="stat-value" style="color: #C62828;">{{ $stats['alpa'] ?? 0 }}</div>
            <div class="stat-sub text-muted">Tanpa keterangan</div>
        </div>
    </div>

    <!-- Student Details -->
    <div class="content-card reveal reveal-delay-2">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <h5 class="content-card-title">Daftar Detail Absensi Siswa</h5>
        </div>
        <div class="content-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 80px;">No</th>
                            <th>Nama Siswa</th>
                            <th>NISN</th>
                            <th class="text-center" style="width: 200px;">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendance->details as $index => $detail)
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $detail->student?->user?->name ?? 'Siswa (Tanpa Nama)' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $detail->student?->nisn ?? '-' }}</span>
                                </td>
                                <td class="text-center d-flex justify-content-center">
                                    @php
                                        $badgeClass = match($detail->status) {
                                            'hadir' => 'status-badge--hadir',
                                            'izin' => 'status-badge--izin',
                                            'sakit' => 'status-badge--sakit',
                                            'alpa' => 'status-badge--alpa',
                                            default => 'status-badge--pdf'
                                        };
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }} text-uppercase px-3 py-2 text-center" style="min-width: 120px; display: inline-flex; justify-content: center;">
                                        {{ $detail->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
