@extends('layouts.lms')

@section('title', 'Absensi')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <h1><i class="fas fa-calendar-check text-warning me-2"></i> Rekam Kehadiran Saya</h1>
            <p>Pantau catatan kehadiran Anda di setiap kelas</p>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="content-card reveal reveal-delay-1">
            <div class="content-card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="empty-state-text">Belum ada data kehadiran. Data kehadiran Anda akan muncul di sini setelah guru mencatat kehadiran di kelas.</div>
                </div>
            </div>
        </div>
    @else
        <!-- Statistics -->
        @php
            $total = $records->count();
            $hadir = $records->where('status', 'hadir')->count();
            $izin = $records->where('status', 'izin')->count();
            $sakit = $records->where('status', 'sakit')->count();
            $alpa = $records->where('status', 'alpa')->count();
            $persentase = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
        @endphp

        <div class="stats-grid reveal reveal-delay-1">
            <div class="stat-card stat-card--behavior">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Kehadiran</div>
                        <div class="stat-value stat-value--green">{{ $total }}</div>
                        <div class="stat-sub">Catatan tercatat</div>
                    </div>
                    <div class="stat-icon-circle stat-icon-circle--green">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card--attendance">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Persentase Hadir</div>
                        <div class="stat-value stat-value--primary">{{ $persentase }}%</div>
                        <div class="stat-sub">Dari total kehadiran</div>
                    </div>
                    <div class="stat-icon-circle stat-icon-circle--deep">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card--grades">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Izin / Sakit</div>
                        <div class="stat-value" style="color: var(--accent);">{{ $izin + $sakit }}</div>
                        <div class="stat-sub">Izin & Sakit</div>
                    </div>
                    <div class="stat-icon-circle stat-icon-circle--gold">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card--behavior">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Alpa</div>
                        <div class="stat-value" style="color: var(--text-muted);">{{ $alpa }}</div>
                        <div class="stat-sub">Ketidakhadiran</div>
                    </div>
                    <div class="stat-icon-circle" style="background: rgba(136, 136, 136, 0.1); color: var(--text-muted);">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="content-card reveal reveal-delay-2">
            <div class="content-card-header">
                <div class="content-card-header-icon">
                    <i class="fas fa-table"></i>
                </div>
                <h5 class="content-card-title">Riwayat Kehadiran</h5>
            </div>
            <div class="content-card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th><i class="fas fa-calendar me-1"></i> Tanggal</th>
                            <th>Status Kehadiran</th>
                            <th><i class="fas fa-book me-1"></i> Mata Pelajaran</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($records as $r)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($r->attendance?->date ?? now())->format('d M Y') }}</strong>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($r->status) {
                                            'hadir' => 'status-badge--hadir',
                                            'izin' => 'status-badge--izin',
                                            'sakit' => 'status-badge--sakit',
                                            'alpa' => 'status-badge--alpa',
                                            'cabut' => 'status-badge--cabut',
                                            default => 'status-badge--pdf'
                                        };
                                        $statusIcon = match($r->status) {
                                            'hadir' => '<i class="fas fa-check me-1"></i>',
                                            'izin' => '<i class="fas fa-file-alt me-1"></i>',
                                            'sakit' => '<i class="fas fa-notes-medical me-1"></i>',
                                            'alpa' => '<i class="fas fa-times me-1"></i>',
                                            'cabut' => '<i class="fas fa-running me-1"></i>',
                                            default => ''
                                        };
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">{!! $statusIcon !!} {{ ucfirst($r->status) }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $r->attendance?->subject_id ?? '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Belum ada data absensi.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">{{ $records->links() }}</div>
    @endif
@endsection
