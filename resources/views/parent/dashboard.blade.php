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
        .transition-all {
            transition: all 0.3s ease;
        }
        .transition-all:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
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

    <main class="container" style="padding-bottom: 50px;">
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

        <!-- ==========================================
             3. PILIHAN RINCIAN AKTIVITAS (3 LARGE INTERACTIVE CARDS)
             ========================================== -->
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <h5 class="fw-bold text-dark mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <i class="fas fa-compass text-success me-2"></i> Pilihan Rincian Aktivitas Anak
                </h5>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.7rem;">
                    Ketuk Card untuk Buka Halaman
                </span>
            </div>
            <p class="text-muted small mb-3">Pilih salah satu menu di bawah ini untuk melihat rincian riwayat lengkap dari aktivitas sekolah anak Anda:</p>
        </div>

        <div class="row g-3">
            <!-- KARTU 1: PRESENSI & KEHADIRAN MAPEL -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden transition-all" style="background-color: #ffffff; border-top: 4px solid #1B5E20 !important;">
                    <div class="card-body p-3.5 p-md-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="p-3 bg-success-subtle text-success rounded-3">
                                <i class="fas fa-book-reader fa-2x"></i>
                            </div>
                            <span class="badge bg-success text-white px-2.5 py-1 rounded-pill small fw-bold">
                                {{ $presentPct }}% Hadir
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            1. Presensi & Kehadiran
                        </h5>
                        <p class="text-muted small mb-3 flex-grow-1">
                            Lihat rincian kehadiran per jam mata pelajaran hari ini serta riwayat presensi hari-hari sebelumnya.
                        </p>
                        <a href="{{ route('parent.presensi') }}" class="btn btn-success w-100 rounded-3 fw-bold py-2.5 d-flex align-items-center justify-content-between" style="background-color: #1B5E20; border: none;">
                            <span><i class="fas fa-eye me-1.5"></i> Lihat Rincian Presensi</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- KARTU 2: PR & TANGGUNGAN TUGAS -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden transition-all" style="background-color: #ffffff; border-top: 4px solid {{ $pendingAssignments->count() > 0 ? '#F57C00' : '#1B5E20' }} !important;">
                    <div class="card-body p-3.5 p-md-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="p-3 bg-warning-subtle text-warning rounded-3">
                                <i class="fas fa-tasks fa-2x"></i>
                            </div>
                            @if($pendingAssignments->count() > 0)
                                <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill small fw-bold">
                                    {{ $pendingAssignments->count() }} PR Belum
                                </span>
                            @else
                                <span class="badge bg-success text-white px-2.5 py-1 rounded-pill small fw-bold">
                                    Semua PR Selesai
                                </span>
                            @endif
                        </div>
                        <h5 class="fw-bold text-dark mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            2. Tanggungan PR & Tugas
                        </h5>
                        <p class="text-muted small mb-3 flex-grow-1">
                            Lihat daftar PR 1 minggu terakhir yang belum dikumpulkan beserta riwayat tugas yang sudah dikerjakan.
                        </p>
                        <a href="{{ route('parent.tugas') }}" class="btn btn-warning text-dark w-100 rounded-3 fw-bold py-2.5 d-flex align-items-center justify-content-between" style="background-color: #F9A825; border: none;">
                            <span><i class="fas fa-list-check me-1.5"></i> Lihat Tanggungan PR</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- KARTU 3: REKAP NILAI & CATATAN RAPOR -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden transition-all" style="background-color: #ffffff; border-top: 4px solid #F9A825 !important;">
                    <div class="card-body p-3.5 p-md-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="p-3 bg-warning-subtle text-warning rounded-3">
                                <i class="fas fa-graduation-cap fa-2x"></i>
                            </div>
                            <span class="badge bg-dark text-white px-2.5 py-1 rounded-pill small font-monospace fw-bold">
                                Avg {{ $avgScore }}/100
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            3. Rekap Nilai & Rapor
                        </h5>
                        <p class="text-muted small mb-3 flex-grow-1">
                            Lihat rekapitulasi nilai tugas, ulangan per mata pelajaran, serta catatan perkembangan belajar anak.
                        </p>
                        <a href="{{ route('parent.nilai') }}" class="btn btn-outline-dark w-100 rounded-3 fw-bold py-2.5 d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-chart-line me-1.5"></i> Lihat Rekap Nilai</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer bg-white border-top py-3 text-center text-muted small">
        <div class="container">
            SMAN 15 Padang &copy; {{ date('Y') }} &middot; Portal Pemantauan Orang Tua
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
