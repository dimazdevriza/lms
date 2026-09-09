<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai & Catatan - Pemantauan Orang Tua</title>
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
                            <span class="header-badge"><i class="fas fa-graduation-cap me-1"></i> Rekap Nilai & Catatan</span>
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
        <div class="p-3 bg-white rounded-4 border shadow-sm mb-4" style="border-left: 5px solid #F9A825 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">RATA-RATA NILAI TUGAS & RAPOR</span>
                    <h2 class="fw-extrabold text-dark font-monospace mb-0">{{ $avgScore }} <span class="fs-6 text-muted font-sans font-weight-normal">/ 100</span></h2>
                    <span class="text-muted small"><i class="fas fa-award text-warning me-1"></i>{{ $gradedTasks }} tugas telah dinilai oleh guru pengampu</span>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle d-none d-sm-block">
                    <i class="fas fa-graduation-cap fa-2x"></i>
                </div>
            </div>
        </div>

        <!-- Nilai Per Mata Pelajaran -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <i class="fas fa-clipboard-list text-success me-2"></i> Rekap Nilai Mata Pelajaran
                </h5>

                @if($grades->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th>Kategori / Jenis Nilai</th>
                                    <th class="text-center">Nilai</th>
                                    <th>Catatan Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grades as $grade)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $grade->subject?->name ?? 'Mata Pelajaran' }}</td>
                                        <td class="small"><span class="badge bg-light text-dark border px-2 py-1">{{ strtoupper($grade->type ?? 'TUGAS') }}</span></td>
                                        <td class="text-center font-monospace fw-bold fs-6 text-success">{{ $grade->score }}</td>
                                        <td class="small text-muted">{{ $grade->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $grades->links() }}
                    </div>
                @else
                    <div class="p-4 bg-light rounded-3 border text-center text-muted">
                        <i class="fas fa-info-circle me-1 text-success"></i> Belum ada rekap nilai resmi yang diinput oleh guru mata pelajaran.
                    </div>
                @endif
            </div>
        </div>

        <!-- Catatan Perilaku & Catatan Wali Kelas -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <i class="fas fa-comment-dots text-success me-2"></i> Catatan Perilaku & Perkembangan Belajar
                </h5>

                @if($behaviorRecords->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($behaviorRecords as $br)
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge {{ $br->type === 'positif' ? 'bg-success' : 'bg-danger' }} px-2.5 py-1 rounded-pill">
                                        {{ strtoupper($br->type) }}
                                    </span>
                                    <span class="small text-muted">{{ \Carbon\Carbon::parse($br->date)->isoFormat('D MMM Y') }}</span>
                                </div>
                                <p class="mb-0 text-dark font-weight-medium fs-6">{{ $br->description }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $behaviorRecords->links() }}
                    </div>
                @else
                    <div class="text-center p-3 text-muted">Belum ada catatan khusus perilaku dari wali kelas / guru.</div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
