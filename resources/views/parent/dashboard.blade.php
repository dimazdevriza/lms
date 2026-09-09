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
             1. EXECUTIVE RECAP CARD (1 LAYAR RINGKAS HP & DESKTOP)
             ========================================== -->
        <div class="card border-0 shadow-lg mb-4 overflow-hidden" style="border-radius: 20px; background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%); color: white;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-white-20">
                    <div>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                            ⚡ REKAP EKSEKUTIF UTAMA
                        </span>
                        <h4 class="fw-bold text-white mb-0 mt-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Ringkasan Aktivitas {{ $student->user->name }}
                        </h4>
                    </div>
                    <div class="text-white-50 small font-monospace d-none d-sm-block text-end">
                        <i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                    </div>
                </div>

                <div class="row g-2">
                    <!-- Point 1: Kehadiran Hari Ini -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                            <div class="text-white-50 small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="fas fa-user-check me-1 text-warning"></i> 1. Presensi Hari Ini
                            </div>
                            <div class="fw-bold fs-6 text-white">
                                @if($todaySubjectAttendances->count() > 0)
                                    @php
                                        $hadirTodayCount = $todaySubjectAttendances->where('status', 'hadir')->count();
                                        $totTodayCount = $todaySubjectAttendances->count();
                                    @endphp
                                    <span class="badge bg-success border border-light px-2 py-1 fs-6">
                                        <i class="fas fa-check-circle me-1"></i> {{ $hadirTodayCount }}/{{ $totTodayCount }} Mapel Hadir
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark px-2 py-1 fs-6">
                                        Belum Ada Absen Mapel
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Point 2: PR Belum Dikerjakan -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                            <div class="text-white-50 small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="fas fa-tasks me-1 text-warning"></i> 2. Tanggungan PR (1 Minggu)
                            </div>
                            <div class="fw-bold fs-6 text-white">
                                @if($pendingAssignments->count() > 0)
                                    <span class="badge bg-danger border border-light px-2 py-1 fs-6">
                                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $pendingAssignments->count() }} PR Belum Selesai
                                    </span>
                                @else
                                    <span class="badge bg-success border border-light px-2 py-1 fs-6">
                                        <i class="fas fa-check-circle me-1"></i> Semua PR Selesai
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Point 3: Rata-Rata Nilai -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                            <div class="text-white-50 small fw-bold text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="fas fa-graduation-cap me-1 text-warning"></i> 3. Rata-Rata Nilai Tugas
                            </div>
                            <div class="fw-bold fs-5 text-white">
                                {{ $avgScore }} <span class="fs-6 text-white-50">/ 100</span>
                                <span class="small text-white-50 ms-1">({{ $gradedTasks }} Tugas)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             NAVIGASI POINT RINCIAN (TAB CLEAN PADA HP)
             ========================================== -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h5 class="fw-bold text-dark mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                <i class="fas fa-list-ul text-success me-2"></i> Pilihan Rincian Aktivitas
            </h5>
            <span class="text-muted small">Pilih menu di bawah untuk melihat detail rincian:</span>
        </div>

        <ul class="nav nav-pills nav-fill bg-white p-2 rounded-4 shadow-sm mb-4 border" id="parentMainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-3 py-2 fw-bold text-start text-sm-center" id="tab-presensi-btn" data-bs-toggle="pill" data-bs-target="#tab-presensi" type="button" role="tab">
                    <i class="fas fa-book-reader me-1 text-success"></i> 1. Presensi Mapel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-3 py-2 fw-bold text-start text-sm-center position-relative" id="tab-pr-btn" data-bs-toggle="pill" data-bs-target="#tab-pr" type="button" role="tab">
                    <i class="fas fa-tasks me-1 text-warning"></i> 2. Tanggungan PR
                    @if($pendingAssignments->count() > 0)
                        <span class="badge bg-danger rounded-circle ms-1">{{ $pendingAssignments->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-3 py-2 fw-bold text-start text-sm-center" id="tab-nilai-btn" data-bs-toggle="pill" data-bs-target="#tab-nilai" type="button" role="tab">
                    <i class="fas fa-graduation-cap me-1 text-primary"></i> 3. Nilai & Catatan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="parentMainTabsContent">
            <!-- TAB 1: PRESENSI MAPEL HARI INI -->
            <div class="tab-pane fade show active" id="tab-presensi" role="tabpanel">
                <div class="today-card">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                <i class="fas fa-book-reader text-success me-2"></i>Kehadiran Mata Pelajaran Hari Ini
                            </h5>
                            <span class="text-muted small">
                                <i class="far fa-clock me-1"></i>Presensi dari Guru Mata Pelajaran &middot; {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                            </span>
                        </div>
                        <div>
                            <a href="#historyModal" data-bs-toggle="modal" class="btn btn-outline-success btn-sm rounded-pill font-weight-bold">
                                <i class="fas fa-history me-1"></i> Riwayat Hari Sebelumnya
                            </a>
                        </div>
                    </div>

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
                                        default => 'today-status--belum'
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
                            <div class="text-muted fw-bold mb-1"><i class="fas fa-info-circle text-primary me-1"></i>Belum Ada Catatan Presensi Mata Pelajaran Hari Ini</div>
                            <span class="text-secondary small">Guru mata pelajaran belum menginput absensi jam pelajaran untuk hari ini.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 2: PR BELUM DIKERJAKAN (SOROTAN 1 MINGGU TERAKHIR) -->
            <div class="tab-pane fade" id="tab-pr" role="tabpanel">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="fw-bold mb-0 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            <i class="fas fa-tasks text-warning me-2"></i> Pekerjaan Rumah (PR) & Tugas Belum Dikerjakan
                        </h5>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-weight-semibold">
                            <i class="fas fa-calendar-week text-success me-1"></i> Sorotan 1 Minggu Terakhir
                        </span>
                    </div>

                    @if($pendingAssignments->count() > 0)
                        <div class="alert alert-warning border-0 rounded-3 mb-3 d-flex align-items-center gap-3 p-3 shadow-sm">
                            <div class="bg-warning text-dark p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink:0;">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                            </div>
                            <div>
                                <strong class="fs-6 text-dark">Ada {{ $pendingAssignments->count() }} PR / Tugas yang belum dikumpulkan oleh anak Anda.</strong>
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

                                    <div class="text-end ms-auto">
                                        <div class="bg-danger text-white px-3 py-2 rounded-3 text-center shadow-sm">
                                            <div class="small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em; text-transform: uppercase;">TENGGAT WAKTU</div>
                                            <div class="fw-extrabold font-monospace fs-6">{{ \Carbon\Carbon::parse($pa->due_at)->format('d M Y') }}</div>
                                            <div class="small font-monospace">{{ \Carbon\Carbon::parse($pa->due_at)->format('H:i') }} WIB</div>
                                        </div>
                                        <div class="text-danger fw-semibold small mt-1">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($pa->due_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="pr-done-banner p-4 bg-light rounded-3 text-center border">
                            <div class="display-6 mb-2">🎉</div>
                            <h5 class="fw-bold mb-1 text-success">Semua PR & Tugas 1 Minggu Terakhir Sudah Selesai!</h5>
                            <p class="mb-0 text-muted small">Anak Anda tidak memiliki tanggungan Pekerjaan Rumah (PR) dari mata pelajaran manapun dalam 1 minggu terakhir.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 3: REKAP NILAI & CATATAN PERILAKU -->
            <div class="tab-pane fade" id="tab-nilai" role="tabpanel">
                <div class="mb-4">
            <h4 class="section-heading">
                <i class="fas fa-graduation-cap"></i> Rekap Nilai & Catatan Wali Kelas
            </h4>

            <ul class="nav nav-tabs nav-tabs-parent mb-3" id="gradeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sub-tab" data-bs-toggle="tab" data-bs-target="#sub-pane" type="button" role="tab">
                        <i class="fas fa-clipboard-check me-1"></i> Nilai Tugas Dikumpulkan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rapor-tab" data-bs-toggle="tab" data-bs-target="#rapor-pane" type="button" role="tab">
                        <i class="fas fa-file-invoice me-1"></i> Nilai Rapor & Ujian
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="note-tab" data-bs-toggle="tab" data-bs-target="#note-pane" type="button" role="tab">
                        <i class="fas fa-star me-1"></i> Catatan Perilaku (Wali Kelas)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="gradeTabsContent">
                <!-- Tab Nilai Tugas -->
                <div class="tab-pane fade show active" id="sub-pane" role="tabpanel">
                    <div class="content-card">
                        <div class="content-card-body p-0">
                            <div class="table-responsive">
                                <table class="table-modern">
                                    <thead>
                                        <tr>
                                            <th>Mata Pelajaran & Tugas</th>
                                            <th>Waktu Dikumpulkan</th>
                                            <th>Nilai</th>
                                            <th>Umpan Balik Guru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($submissions as $sub)
                                            <tr>
                                                <td>
                                                    <span class="cell-primary">{{ $sub->assignment?->title }}</span>
                                                    <div class="cell-secondary">{{ $sub->assignment?->subject?->name }}</div>
                                                </td>
                                                <td>
                                                    <span class="submit-badge"><i class="fas fa-check-circle"></i> Dikumpul</span>
                                                    <div class="cell-secondary">{{ \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, H:i') }}</div>
                                                </td>
                                                <td>
                                                    @if($sub->score !== null)
                                                        <span class="score-badge">{{ $sub->score }}</span>
                                                    @else
                                                        <span class="pending-badge">Belum Dinilai</span>
                                                    @endif
                                                </td>
                                                <td class="cell-secondary">
                                                    {{ $sub->feedback ? '"'.$sub->feedback.'"' : '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Belum ada nilai tugas yang dikumpulkan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Nilai Rapor -->
                <div class="tab-pane fade" id="rapor-pane" role="tabpanel">
                    <div class="content-card">
                        <div class="content-card-body p-0">
                            <div class="table-responsive">
                                <table class="table-modern">
                                    <thead>
                                        <tr>
                                            <th>Mata Pelajaran</th>
                                            <th>Jenis Penilaian</th>
                                            <th>Tanggal Penilaian</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($grades as $grade)
                                            <tr>
                                                <td class="cell-primary">{{ $grade->subject?->name }}</td>
                                                <td><span class="type-badge">{{ ucfirst($grade->assessment_type) }}</span></td>
                                                <td class="cell-secondary">{{ \Carbon\Carbon::parse($grade->assessment_date)->format('d M Y') }}</td>
                                                <td><span class="score-badge">{{ $grade->score }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Belum ada input nilai dari wali kelas/guru.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Catatan Perilaku -->
                <div class="tab-pane fade" id="note-pane" role="tabpanel">
                    <div class="content-card">
                        <div class="content-card-body">
                            <div class="row g-3">
                                @forelse($behaviorRecords as $br)
                                    <div class="col-md-6">
                                        <div class="behavior-card behavior-card--{{ $br->type === 'positif' ? 'prestasi' : 'pelanggaran' }}">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="behavior-card-title behavior-card-title--{{ $br->type === 'positif' ? 'prestasi' : 'pelanggaran' }} mb-0">
                                                    {{ $br->title }}
                                                </h6>
                                                <span class="behavior-type-badge behavior-type-badge--{{ $br->type === 'positif' ? 'prestasi' : 'pelanggaran' }}">
                                                    {{ $br->type === 'positif' ? 'Positif' : 'Teguran' }}
                                                </span>
                                            </div>
                                            <p class="behavior-card-desc mb-2">{{ $br->description }}</p>
                                            <div class="behavior-card-date text-muted small">
                                                <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($br->date)->format('d M Y') }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-4">
                                        <i class="fas fa-heart text-danger fa-2x mb-2 d-block"></i>
                                        Tidak ada catatan khusus. Perilaku anak Anda sangat baik di sekolah.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ==========================================
         MODAL: RIWAYAT KEHADIRAN HARI SEBELUMNYA
         ========================================== -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: var(--radius-lg);">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="historyModalLabel">
                        <i class="fas fa-history me-2"></i>Riwayat Kehadiran Anak (Hari-Hari Sebelumnya)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nav Tabs Riwayat -->
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-daily-tab" data-bs-toggle="pill" data-bs-target="#pills-daily" type="button" role="tab">Absensi Harian Sekolah</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-subject-tab" data-bs-toggle="pill" data-bs-target="#pills-subject" type="button" role="tab">Absensi Per Mata Pelajaran</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-daily" role="tabpanel">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Status Kehadiran</th>
                                        <th>Catatan Wali Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dailyAttendances as $da)
                                        <tr>
                                            <td class="fw-bold">{{ \Carbon\Carbon::parse($da->attendance?->date)->format('d M Y') }}</td>
                                            <td><span class="status-badge status-badge--{{ strtolower($da->status) }}">{{ ucfirst($da->status) }}</span></td>
                                            <td class="text-muted small">{{ $da->note ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">Belum ada data riwayat absensi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-2">{{ $dailyAttendances->links('pagination::bootstrap-5') }}</div>
                        </div>

                        <div class="tab-pane fade" id="pills-subject" role="tabpanel">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Mata Pelajaran & Guru</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subjectAttendances as $sa)
                                        <tr>
                                            <td class="fw-bold">{{ \Carbon\Carbon::parse($sa->attendance?->date)->format('d M Y') }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $sa->attendance?->subject?->name }}</div>
                                                <div class="text-muted small">Guru: {{ $sa->attendance?->teacher?->user->name }}</div>
                                            </td>
                                            <td><span class="status-badge status-badge--{{ strtolower($sa->status) }}">{{ ucfirst($sa->status) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">Belum ada data riwayat presensi mapel.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-2">{{ $subjectAttendances->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <span class="footer-logo-text">SMA Negeri 15 Padang</span>
                </div>
                <p class="footer-copy">&copy; {{ date('Y') }} LMS SMA Negeri 15 Padang. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
