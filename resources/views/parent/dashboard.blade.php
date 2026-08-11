<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pemantauan Orang Tua - {{ $student->user->name }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/parent-dashboard.css') }}">
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
                                <i class="fas fa-child"></i> Profil Siswa Pantauan
                            </span>
                            <h2 class="header-name">{{ $student->user->name }}</h2>
                            <p class="header-meta mb-0">
                                Kelas: <strong>{{ $student->schoolClass?->name ?? '-' }}</strong>
                                <span class="d-none d-sm-inline mx-1">&middot;</span>
                                <br class="d-sm-none">
                                NISN: <strong>{{ $student->nisn }}</strong>
                                <span class="d-none d-sm-inline mx-1">&middot;</span>
                                <br class="d-sm-none">
                                Tahun Ajaran: <strong>{{ $student->schoolClass?->academicYear?->name ?? '-' }}</strong>
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

    <main class="container" style="padding-bottom: 20px;">
        @if(session('success'))
            <div class="alert alert-custom alert-dismissible fade show reveal" role="alert" style="margin-top: 8px;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Quick Summary Cards -->
        <div class="stats-grid" style="margin-top: 12px;">
            @php
                $presentPct = $totDaily > 0 ? round(($hadirDaily / $totDaily) * 100, 1) : 100;
            @endphp

            <div class="stat-card stat-card--attendance reveal reveal-delay-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Kehadiran Harian</div>
                        <div class="stat-value stat-value--green">{{ $presentPct }}%</div>
                        <div class="stat-sub">{{ $hadirDaily }} dari {{ $totDaily }} hari</div>
                    </div>
                    <div class="stat-icon-circle stat-icon-circle--green">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card--grades reveal reveal-delay-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Rata-Rata Nilai Tugas</div>
                        <div class="stat-value stat-value--primary">{{ $avgScore }}</div>
                        <div class="stat-sub">{{ $gradedTasks }} tugas dinilai</div>
                    </div>
                    <div class="stat-icon-circle stat-icon-circle--gold">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-card--behavior reveal reveal-delay-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Catatan Wali Kelas</div>
                        <div class="stat-value stat-value--primary">{{ $totalBehaviors }}</div>
                        <div class="stat-sub">
                            <span class="text-success">{{ $goodBehaviors }} Prestasi</span> &middot;
                            <span class="text-danger">{{ $badBehaviors }} Teguran</span>
                        </div>
                    </div>
                    <div class="stat-icon-circle stat-icon-circle--deep">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs-wrapper reveal reveal-delay-4">
            <ul class="nav nav-underline-custom" id="parentTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button" role="tab" aria-controls="attendance-pane" aria-selected="true">
                        <i class="fas fa-clipboard-check me-2"></i>Kehadiran Anak
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades-pane" type="button" role="tab" aria-controls="grades-pane" aria-selected="false">
                        <i class="fas fa-graduation-cap me-2"></i>Tugas & Nilai
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="behavior-tab" data-bs-toggle="tab" data-bs-target="#behavior-pane" type="button" role="tab" aria-controls="behavior-pane" aria-selected="false">
                        <i class="fas fa-award me-2"></i>Catatan Wali Kelas
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content reveal reveal-delay-5" id="parentTabContent">
            
            <!-- Kehadiran Pane -->
            <div class="tab-pane fade show active" id="attendance-pane" role="tabpanel" aria-labelledby="attendance-tab">
                <div class="row g-4">
                    <!-- Daily Attendance (Wali Kelas) -->
                    <div class="col-lg-6">
                        <div class="content-card h-100">
                            <div class="content-card-header">
                                <div class="content-card-header-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <h5 class="content-card-title">Kehadiran Harian Wali Kelas</h5>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($dailyAttendances as $da)
                                                <tr>
                                                    <td class="cell-primary">{{ \Carbon\Carbon::parse($da->attendance?->date)->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="status-badge status-badge--{{ $da->status }}">{{ ucfirst($da->status) }}</span>
                                                    </td>
                                                    <td class="cell-secondary">{{ $da->note ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="empty-state">
                                                            <div class="empty-state-icon">
                                                                <i class="fas fa-calendar-day"></i>
                                                            </div>
                                                            <div class="empty-state-text">Belum ada catatan absensi harian untuk saat ini</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $dailyAttendances->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Attendance -->
                    <div class="col-lg-6">
                        <div class="content-card h-100">
                            <div class="content-card-header">
                                <div class="content-card-header-icon">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <h5 class="content-card-title">Kehadiran Sesi Mata Pelajaran</h5>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subjectAttendances as $sa)
                                                <tr>
                                                    <td class="cell-primary">{{ \Carbon\Carbon::parse($sa->attendance?->date)->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="cell-primary">{{ $sa->attendance?->subject?->name }}</span>
                                                        <div class="cell-secondary">Guru: {{ $sa->attendance?->teacher?->user->name }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-badge--{{ $sa->status }}">{{ ucfirst($sa->status) }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="empty-state">
                                                            <div class="empty-state-icon">
                                                                <i class="fas fa-book-open"></i>
                                                            </div>
                                                            <div class="empty-state-text">Belum ada catatan absensi mata pelajaran</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $subjectAttendances->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tugas & Nilai Pane -->
            <div class="tab-pane fade" id="grades-pane" role="tabpanel" aria-labelledby="grades-tab">
                <div class="row g-4">
                    <!-- Assignment Submissions (Tugas & Latihan) -->
                    <div class="col-lg-7">
                        <div class="content-card h-100">
                            <div class="content-card-header">
                                <div class="content-card-header-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <h5 class="content-card-title">Status Pengumpulan Tugas</h5>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Nama Tugas</th>
                                                <th>Pengumpulan</th>
                                                <th>Nilai / Umpan Balik</th>
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
                                                        <span class="submit-badge">
                                                            <i class="fas fa-check-circle"></i> Dikumpul
                                                        </span>
                                                        <div class="cell-secondary" style="margin-top: 4px;">{{ \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, H:i') }}</div>
                                                    </td>
                                                    <td>
                                                        @if($sub->score !== null)
                                                            <span class="score-badge">{{ $sub->score }}</span>
                                                            @if($sub->feedback)
                                                                <div class="feedback-text">"{{ Str::limit($sub->feedback, 50) }}"</div>
                                                            @endif
                                                        @else
                                                            <span class="pending-badge">Belum Dinilai</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="empty-state">
                                                            <div class="empty-state-icon">
                                                                <i class="fas fa-clipboard-list"></i>
                                                            </div>
                                                            <div class="empty-state-text">Belum ada tugas yang dikumpulkan oleh siswa</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $submissions->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Nilai Rapor oleh Wali Kelas -->
                    <div class="col-lg-5">
                        <div class="content-card h-100">
                            <div class="content-card-header">
                                <div class="content-card-header-icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <h5 class="content-card-title">Nilai Akademik Terinput</h5>
                            </div>
                            <div class="content-card-body">
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Mapel</th>
                                                <th>Jenis Nilai</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($grades as $grade)
                                                <tr>
                                                    <td class="cell-primary">{{ $grade->subject?->name }}</td>
                                                    <td>
                                                        <span class="type-badge">{{ ucfirst($grade->assessment_type) }}</span>
                                                        <div class="cell-secondary" style="margin-top: 4px;">{{ \Carbon\Carbon::parse($grade->assessment_date)->format('d/m/Y') }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="score-badge">{{ $grade->score }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3">
                                                        <div class="empty-state">
                                                            <div class="empty-state-icon">
                                                                <i class="fas fa-file-alt"></i>
                                                            </div>
                                                            <div class="empty-state-text">Belum ada nilai terinput dari wali kelas</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $grades->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Perilaku Pane -->
            <div class="tab-pane fade" id="behavior-pane" role="tabpanel" aria-labelledby="behavior-tab">
                <div class="content-card">
                    <div class="content-card-header">
                        <div class="content-card-header-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5 class="content-card-title">Catatan Perilaku & Karakter Siswa</h5>
                    </div>
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
                                                {{ $br->type === 'positif' ? 'Positif' : 'Negatif' }}
                                            </span>
                                        </div>
                                        <p class="behavior-card-desc">{{ $br->description }}</p>
                                        <div class="behavior-card-date">
                                            <i class="fas fa-calendar-alt me-1"></i> Tanggal Kejadian: {{ \Carbon\Carbon::parse($br->date)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="empty-state" style="padding: 56px 24px;">
                                        <div class="empty-state-icon" style="width: 80px; height: 80px;">
                                            <i class="fas fa-heart" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="empty-state-text" style="max-width: 320px;">
                                            Tidak ada catatan khusus. Perilaku siswa baik dan kondusif sepanjang semester ini.
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            {{ $behaviorRecords->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>

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
                <p class="footer-copy">&copy; {{ date('Y') }} LMS SMA Negeri 15 Padang. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('sub_page') || urlParams.has('grade_page')) {
                const gradesTab = document.getElementById('grades-tab');
                if (gradesTab) {
                    const tab = new bootstrap.Tab(gradesTab);
                    tab.show();
                }
            } else if (urlParams.has('behavior_page')) {
                const behaviorTab = document.getElementById('behavior-tab');
                if (behaviorTab) {
                    const tab = new bootstrap.Tab(behaviorTab);
                    tab.show();
                }
            } else if (urlParams.has('daily_page') || urlParams.has('subject_page')) {
                const attendanceTab = document.getElementById('attendance-tab');
                if (attendanceTab) {
                    const tab = new bootstrap.Tab(attendanceTab);
                    tab.show();
                }
            }
        });
    </script>
</body>
</html>
