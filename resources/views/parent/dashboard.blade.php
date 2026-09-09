<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemantauan Orang Tua - {{ $student->user->name }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/parent-dashboard.css') }}">
    <style>
        .today-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 2px solid rgba(27, 94, 32, 0.12);
            box-shadow: 0 8px 30px rgba(27, 94, 32, 0.08);
            padding: 24px 28px;
            margin-bottom: 28px;
        }

        .today-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 100px;
            font-size: 1.1rem;
            font-weight: 800;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .today-status--hadir { background: #E8F5E9; color: #1B5E20; border: 1px solid #A5D6A7; }
        .today-status--izin { background: #FFF8E1; color: #F57F17; border: 1px solid #FFE082; }
        .today-status--sakit { background: #FFF3E0; color: #E65100; border: 1px solid #FFCC80; }
        .today-status--alpa { background: #FFEBEE; color: #C62828; border: 1px solid #EF9A9A; }
        .today-status--belum { background: #F5F5F5; color: #616161; border: 1px solid #E0E0E0; }

        .section-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--text-heading);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-heading i {
            color: var(--primary);
        }

        .pr-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            border: 1px solid #E0E0E0;
            border-left: 5px solid var(--accent);
            padding: 18px 20px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }
        .pr-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }
        .pr-badge {
            background: #FFF8E1;
            color: #F57F17;
            border: 1px solid #FFE082;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .pr-done-banner {
            background: linear-gradient(135deg, #E8F5E9, #F1F8E9);
            border: 1px solid #C8E6C9;
            border-radius: var(--radius-md);
            padding: 24px;
            text-align: center;
            color: #1B5E20;
        }

        .nav-tabs-parent .nav-link {
            font-weight: 700;
            color: #666;
            border: none;
            padding: 12px 20px;
            border-radius: var(--radius-sm);
        }
        .nav-tabs-parent .nav-link.active {
            background: var(--primary);
            color: #fff;
        }

        /* Strict No-Blue Override for Parent Portal */
        .nav-pills .nav-link.active,
        .nav-pills .show > .nav-link {
            background-color: #1B5E20 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(27, 94, 32, 0.3) !important;
        }
        .nav-pills .nav-link {
            color: #212529 !important;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            margin: 2px;
        }
        .nav-pills .nav-link:hover {
            background-color: #e8f5e9 !important;
        }
        .text-primary, .btn-primary {
            color: #1B5E20 !important;
        }
        .bg-primary {
            background-color: #1B5E20 !important;
        }
        .border-primary {
            border-color: #1B5E20 !important;
        }
        .btn-outline-primary {
            color: #1B5E20 !important;
            border-color: #1B5E20 !important;
        }
        .btn-outline-primary:hover {
            background-color: #1B5E20 !important;
            color: #ffffff !important;
        }
        .badge.bg-primary {
            background-color: #1B5E20 !important;
        }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <header class="header-banner">
        <div class="header-inner">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-ring d-none d-sm-block">
                            <div class="avatar-inner">
                                {{ strtoupper(substr($student->user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <span class="header-badge">
                                <i class="fas fa-child"></i> Pemantauan Orang Tua
                            </span>
                            <h2 class="header-name">{{ $student->user->name }}</h2>
                            <p class="header-meta mb-0">
                                Kelas: <strong>{{ $student->schoolClass?->name ?? '-' }}</strong>
                                <span class="d-none d-sm-inline mx-1">&middot;</span>
                                NISN: <strong>{{ $student->nisn }}</strong>
                            </p>
                        </div>
                    </div>
                    <div>
                        <form action="{{ route('parent.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-wave">
            <svg viewBox="0 0 1440 40" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 40h1440V16c-200 16-400 24-720 20S200 8 0 24v16z" fill="#FAFAF7"/>
            </svg>
        </div>
    </header>

    <main class="container" style="padding-bottom: 40px;">
        @if(session('success'))
            <div class="alert alert-custom alert-dismissible fade show mt-2" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- ==========================================
             1. PERSENTASE KESELURUHAN (CLEAN 3-CARD PROGRESS GRID)
             ========================================== -->
        @php
            $totSubjectCount = isset($totSubject) ? $totSubject : 0;
            $hadirSubjectCount = isset($hadirSubject) ? $hadirSubject : 0;
            $presentPct = $totSubjectCount > 0 ? round(($hadirSubjectCount / $totSubjectCount) * 100, 1) : 100;
            $totalAss = $submissions->count() + $pendingAssignments->count();
            $taskDonePct = $totalAss > 0 ? round(($submissions->count() / $totalAss) * 100) : 100;
        @endphp

        <div class="row g-2 mb-3">
            <!-- 1. PERSENTASE KEHADIRAN SELURUH MATA PELAJARAN -->
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white rounded-4 border shadow-sm h-100 position-relative overflow-hidden" style="border-left: 5px solid #1B5E20 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                KEHADIRAN MATA PELAJARAN
                            </div>
                            <div class="fs-3 fw-extrabold text-success font-monospace my-1">
                                {{ $presentPct }}%
                            </div>
                        </div>
                        <div class="bg-success-subtle text-success p-2 rounded-circle">
                            <i class="fas fa-book-reader"></i>
                        </div>
                    </div>
                    <div class="progress rounded-pill bg-light mb-2" style="height: 7px;">
                        <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $presentPct }}%"></div>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-check-circle text-success me-1"></i>{{ $hadirSubjectCount }} dari {{ $totSubjectCount }} jam mapel hadir
                    </div>
                </div>
            </div>

            <!-- 2. PERSENTASE PENGUMPULAN TUGAS -->
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white rounded-4 border shadow-sm h-100 position-relative overflow-hidden" style="border-left: 5px solid {{ $pendingAssignments->count() > 0 ? '#F57C00' : '#1B5E20' }} !important;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                PENGUMPULAN TUGAS
                            </div>
                            <div class="fs-3 fw-extrabold {{ $pendingAssignments->count() > 0 ? 'text-warning' : 'text-success' }} font-monospace my-1">
                                {{ $taskDonePct }}%
                            </div>
                        </div>
                        <div class="bg-warning-subtle text-warning p-2 rounded-circle">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                    <div class="progress rounded-pill bg-light mb-2" style="height: 7px;">
                        <div class="progress-bar {{ $pendingAssignments->count() > 0 ? 'bg-warning' : 'bg-success' }} rounded-pill" role="progressbar" style="width: {{ $taskDonePct }}%"></div>
                    </div>
                    <div class="text-muted small">
                        @if($pendingAssignments->count() > 0)
                            <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Ada {{ $pendingAssignments->count() }} PR Belum Dikerjakan</span>
                        @else
                            <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Semua PR 100% Dikumpulkan</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. RATA-RATA NILAI -->
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white rounded-4 border shadow-sm h-100 position-relative overflow-hidden" style="border-left: 5px solid #F9A825 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                RATA-RATA NILAI TUGAS
                            </div>
                            <div class="fs-3 fw-extrabold text-dark font-monospace my-1">
                                {{ $avgScore }} <span class="fs-6 text-muted font-monospace">/ 100</span>
                            </div>
                        </div>
                        <div class="bg-warning-subtle text-warning p-2 rounded-circle">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="progress rounded-pill bg-light mb-2" style="height: 7px;">
                        <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: {{ min(100, $avgScore) }}%"></div>
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-award text-warning me-1"></i>{{ $gradedTasks }} tugas telah dinilai guru
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             2. EXECUTIVE RECAP HIGHLIGHT (SOROTAN AKTIVITAS ANAK)
             ========================================== -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 20px; background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%); color: white;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-white-20">
                    <div>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            ⚡ HIGHLIGHT AKTIVITAS TERBARU
                        </span>
                        <h5 class="fw-bold text-white mb-0 mt-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Sorotan Hari Ini & 1 Minggu Terakhir
                        </h5>
                    </div>
                    <div class="text-white-50 small font-monospace d-none d-sm-block text-end">
                        <i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                    </div>
                </div>

                <div class="row g-2">
                    <!-- Point 1: Kehadiran Hari Ini -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                            <div class="text-white-50 small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="fas fa-user-check me-1 text-warning"></i> Presensi Jam Pelajaran Hari Ini
                            </div>
                            <div class="fw-bold fs-6 text-white">
                                @if($todaySubjectAttendances->count() > 0)
                                    @php
                                        $hadirTodayCount = $todaySubjectAttendances->where('status', 'hadir')->count();
                                        $totTodayCount = $todaySubjectAttendances->count();
                                    @endphp
                                    <span class="badge bg-success border border-light px-2 py-1 fs-6">
                                        <i class="fas fa-check-circle me-1"></i> {{ $hadirTodayCount }}/{{ $totTodayCount }} Mapel Hadir Hari Ini
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark px-2 py-1 fs-6">
                                        Belum Ada Input Presensi Hari Ini
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Point 2: PR Belum Dikerjakan -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                            <div class="text-white-50 small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="fas fa-tasks me-1 text-warning"></i> Tanggungan PR Belum Dikumpulkan
                            </div>
                            <div class="fw-bold fs-6 text-white">
                                @if($pendingAssignments->count() > 0)
                                    <span class="badge bg-danger border border-light px-2 py-1 fs-6">
                                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $pendingAssignments->count() }} PR Belum Selesai (1 Minggu)
                                    </span>
                                @else
                                    <span class="badge bg-success border border-light px-2 py-1 fs-6">
                                        <i class="fas fa-check-circle me-1"></i> Tidak Ada Tanggungan PR
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                                <span class="d-block small text-secondary">Berikut rincian Mata Pelajaran yang memiliki tanggungan tugas belum dikerjakan dalam 1 minggu terakhir:</span>
                            </div>
                        </div>

                        @foreach($pendingAssignments as $pa)
                            <div class="pr-card mb-3 p-3 bg-white rounded-3 shadow-sm border border-warning" style="border-left: 6px solid #f57c00 !important;">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div class="flex-grow-1">
                                        <!-- HIGHLIGHT MATA PELAJARAN -->
                                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                            <span class="badge bg-success px-3 py-2 fs-6 rounded-pill text-white fw-bold">
                                                <i class="fas fa-book me-1"></i> MAPEL: {{ strtoupper($pa->subject?->name ?? 'Mata Pelajaran') }}
                                            </span>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-2 fw-semibold" style="font-size: 0.8rem;">
                                                <i class="fas fa-times-circle me-1"></i> BELUM DIKUMPULKAN
                                            </span>
                                        </div>
                                        
                                        <h5 class="fw-bold text-dark mb-1 fs-5">{{ $pa->title }}</h5>
                                        <div class="text-muted small mb-2"><i class="fas fa-user-tie me-1"></i>Guru Pengampu: <strong>{{ $pa->teacher?->user->name ?? '-' }}</strong></div>
                                        
                                        @if($pa->description)
                                            <p class="text-secondary small mb-1 p-2 bg-light rounded-2" style="max-width: 750px;">
                                                {{ Str::limit(strip_tags($pa->description), 160) }}
                                            </p>
                                        @endif
                                    </div>

    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <span class="footer-logo-text">SMA Negeri 15 Padang</span>
                </div>
                <p class="footer-copy">&copy; {{ date('Y') }} LMS SMA Negeri 15 Padang. Portal Pemantauan Orang Tua.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
