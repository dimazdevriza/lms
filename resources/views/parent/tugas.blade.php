<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanggungan PR & Tugas - Pemantauan Orang Tua</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/parent-dashboard.css') }}">
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
                            <span class="header-badge"><i class="fas fa-tasks me-1"></i> Tanggungan PR & Tugas</span>
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
        <div class="p-3 bg-white rounded-4 border shadow-sm mb-4" style="border-left: 5px solid {{ $pendingAssignments->count() > 0 ? '#F57C00' : '#1B5E20' }} !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">STATUS TANGGUNGAN PR & TUGAS (1 MINGGU TERAKHIR)</span>
                    <h3 class="fw-bold mb-0 mt-1 {{ $pendingAssignments->count() > 0 ? 'text-warning' : 'text-success' }}">
                        @if($pendingAssignments->count() > 0)
                            <i class="fas fa-exclamation-triangle me-1"></i> Ada {{ $pendingAssignments->count() }} PR Belum Dikerjakan
                        @else
                            <i class="fas fa-check-circle me-1"></i> Semua PR 1 Minggu Terakhir Sudah Selesai!
                        @endif
                    </h3>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle d-none d-sm-block">
                    <i class="fas fa-tasks fa-2x"></i>
                </div>
            </div>
        </div>

        <!-- PR & Tugas Belum Dikerjakan (Sorotan 1 Minggu) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="fw-bold text-dark mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        <i class="fas fa-exclamation-circle text-warning me-2"></i> Daftar PR Belum Dikumpulkan
                    </h5>
                    <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-1.5 rounded-pill font-weight-semibold">
                        <i class="fas fa-calendar-week me-1"></i> Aktivitas 1 Minggu Terakhir
                    </span>
                </div>

                @if($pendingAssignments->count() > 0)
                    <div class="row g-3">
                        @foreach($pendingAssignments as $pa)
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-white rounded-3 border shadow-sm h-100 position-relative" style="border-left: 5px solid #C62828 !important;">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge bg-dark text-white px-2.5 py-1 rounded-2 fw-bold" style="font-size: 0.8rem; letter-spacing: 0.03em;">
                                            📘 MAPEL: {{ strtoupper($pa->subject?->name ?? 'UMUM') }}
                                        </span>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">
                                            ⚠️ Belum Dikerjakan
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1 fs-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                        {{ $pa->title }}
                                    </h6>
                                    <p class="text-muted small mb-2 line-clamp-2" style="font-size: 0.85rem;">
                                        {{ Str::limit(strip_tags($pa->description), 90) }}
                                    </p>
                                    <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-1 mt-auto">
                                        <span class="text-muted small">
                                            <i class="fas fa-user-tie me-1"></i>Guru: <strong>{{ $pa->teacher?->user->name ?? '-' }}</strong>
                                        </span>
                                        <span class="text-danger fw-bold small">
                                            <i class="far fa-clock me-1"></i>Tenggat: {{ $pa->due_at ? \Carbon\Carbon::parse($pa->due_at)->isoFormat('D MMM Y, HH:mm') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-success-subtle text-success rounded-3 border border-success-subtle text-center">
                        <div class="fw-bold fs-6 mb-1"><i class="fas fa-check-circle me-1"></i>Luar Biasa! Tidak Ada PR yang Tertunggak</div>
                        <span class="small">Anak Anda telah menyelesaikan semua tugas dan PR untuk minggu ini.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat Pengumpulan Tugas -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <i class="fas fa-history text-success me-2"></i> Riwayat Pengumpulan & Nilai Tugas Selesai
                </h5>

                @if($submissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th>Judul Tugas</th>
                                    <th>Waktu Dikerjakan</th>
                                    <th class="text-center">Nilai Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $sub)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $sub->assignment?->subject?->name ?? '-' }}</td>
                                        <td class="small">{{ $sub->assignment?->title ?? '-' }}</td>
                                        <td class="small text-muted">{{ \Carbon\Carbon::parse($sub->submitted_at)->isoFormat('D MMM Y, HH:mm') }}</td>
                                        <td class="text-center">
                                            @if($sub->score !== null)
                                                <span class="badge bg-success px-3 py-1 fs-6 font-monospace">{{ $sub->score }} / 100</span>
                                            @else
                                                <span class="badge bg-warning text-dark px-2.5 py-1">Belum Dinilai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $submissions->links() }}
                    </div>
                @else
                    <div class="text-center p-4 text-muted">Belum ada riwayat pengumpulan tugas.</div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
