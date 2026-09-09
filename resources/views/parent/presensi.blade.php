<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kehadiran Mata Pelajaran - Pemantauan Orang Tua</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/parent-dashboard.css') }}">
    <style>
        .nav-pills .nav-link.active {
            background-color: #1B5E20 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body style="background-color: #FAFAF7;">

    <!-- Header Banner -->
    <header class="header-banner">
        <div class="header-inner">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-light btn-sm rounded-circle p-2" title="Kembali ke Dashboard">
                            <i class="fas fa-arrow-left fa-lg"></i>
                        </a>
                        <div>
                            <span class="header-badge"><i class="fas fa-book-reader me-1"></i> Rincian Presensi</span>
                            <h3 class="header-name mb-0">{{ $student->user->name }}</h3>
                            <p class="header-meta mb-0">Kelas: {{ $student->schoolClass?->name ?? '-' }} &middot; NISN: {{ $student->nisn }}</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('parent.dashboard') }}" class="btn btn-light btn-sm rounded-pill font-weight-bold px-3 text-dark">
                            <i class="fas fa-home me-1 text-success"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container py-4" style="padding-bottom: 50px;">
        
        <!-- Action Back Link -->
        <div class="mb-3">
            <a href="{{ route('parent.dashboard') }}" class="text-decoration-none fw-bold small text-success">
                <i class="fas fa-chevron-left me-1"></i> Kembali ke Dashboard Utama
            </a>
        </div>

        <!-- Metric Summary Bar -->
        @php
            $presentPct = $totSubject > 0 ? round(($hadirSubject / $totSubject) * 100, 1) : 100;
        @endphp
        <div class="p-3 bg-white rounded-4 border shadow-sm mb-4" style="border-left: 5px solid #1B5E20 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">REKAP KEHADIRAN MATA PELAJARAN</span>
                    <h2 class="fw-extrabold text-success font-monospace mb-0">{{ $presentPct }}% <span class="fs-6 text-muted font-sans font-weight-normal">Hadir</span></h2>
                    <span class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i>{{ $hadirSubject }} dari {{ $totSubject }} total jam pelajaran hadir</span>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle d-none d-sm-block">
                    <i class="fas fa-book-reader fa-2x"></i>
                </div>
            </div>
        </div>

        <!-- Presensi Hari Ini -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <i class="fas fa-calendar-day text-success me-2"></i> Kehadiran Jam Pelajaran Hari Ini ({{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }})
                </h5>

                @if($todaySubjectAttendances->count() > 0)
                    <div class="row g-3">
                        @foreach($todaySubjectAttendances as $tsa)
                            @php
                                $status = strtolower($tsa->status);
                                $badgeClass = match($status) {
                                    'hadir' => 'today-status--hadir',
                                    'izin' => 'today-status--izin',
                                    'sakit' => 'today-status--sakit',
                                    'alpa', 'cabut' => 'today-status--alpa',
                                    default => 'today-status--alpa'
                                };
                                $icon = match($status) {
                                    'hadir' => 'fa-check-circle',
                                    'izin' => 'fa-envelope',
                                    'sakit' => 'fa-notes-medical',
                                    'alpa', 'cabut' => 'fa-times-circle',
                                    default => 'fa-minus-circle'
                                };
                            @endphp
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark fs-6 mb-1">{{ $tsa->attendance?->subject?->name }}</div>
                                        <div class="text-muted small"><i class="fas fa-user-tie me-1"></i>Guru: {{ $tsa->attendance?->teacher?->user->name ?? 'Guru Pengampu' }}</div>
                                    </div>
                                    <div>
                                        <span class="today-status-badge {{ $badgeClass }}" style="font-size: 0.9rem; padding: 6px 14px;">
                                            <i class="fas {{ $icon }}"></i> {{ strtoupper($status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-light rounded-3 border text-center">
                        <div class="text-muted fw-bold mb-1"><i class="fas fa-info-circle text-success me-1"></i>Belum Ada Catatan Presensi Hari Ini</div>
                        <span class="text-secondary small">Guru mata pelajaran belum menginput absensi jam pelajaran untuk hari ini.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat Presensi Guru Mapel (Tabel/List) -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <i class="fas fa-history text-success me-2"></i> Riwayat Presensi Mata Pelajaran Lengkap
                </h5>

                @if($subjectAttendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengampu</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectAttendances as $sa)
                                    @php
                                        $st = strtolower($sa->status);
                                        $bCls = match($st) {
                                            'hadir' => 'bg-success text-white',
                                            'izin' => 'bg-info text-white',
                                            'sakit' => 'bg-warning text-dark',
                                            'alpa', 'cabut' => 'bg-danger text-white',
                                            default => 'bg-secondary text-white'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="fw-bold small">{{ \Carbon\Carbon::parse($sa->attendance?->date)->isoFormat('D MMM Y') }}</td>
                                        <td class="fw-bold text-dark">{{ $sa->attendance?->subject?->name ?? 'Mata Pelajaran' }}</td>
                                        <td class="small text-muted">{{ $sa->attendance?->teacher?->user->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $bCls }} px-3 py-1 rounded-pill">{{ strtoupper($st) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $subjectAttendances->links() }}
                    </div>
                @else
                    <div class="text-center p-4 text-muted">Belum ada riwayat presensi mata pelajaran.</div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
